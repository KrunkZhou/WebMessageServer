package cn.krunk.msgserver;

import android.annotation.SuppressLint;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.SharedPreferences;
import android.content.pm.PackageManager;
import android.os.Handler;
import android.support.v4.app.ActivityCompat;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.text.InputType;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.widget.EditText;
import android.widget.TextView;
import android.Manifest;
import android.widget.Toast;

import com.android.volley.Cache;
import com.android.volley.Network;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.BasicNetwork;
import com.android.volley.toolbox.DiskBasedCache;
import com.android.volley.toolbox.HurlStack;
import com.android.volley.toolbox.StringRequest;

import java.util.HashMap;
import java.util.Map;

public class MainActivity extends AppCompatActivity {

    Receiver connectionReceiver;
    IntentFilter intentFilter;
    SharedPreferences sharedPref;

    public void checkPermission(String permission, int requestCode) {
        // Checking if permission is not granted
        if (ContextCompat.checkSelfPermission(MainActivity.this, permission) == PackageManager.PERMISSION_DENIED) {
            ActivityCompat.requestPermissions(MainActivity.this, new String[] { permission }, requestCode);
        }
    }

    public void checkMultiPermission(){
        checkPermission(Manifest.permission.RECEIVE_SMS, 101);
        checkPermission(Manifest.permission.WRITE_EXTERNAL_STORAGE, 102);
        checkPermission(Manifest.permission.INTERNET, 103);
        checkPermission(Manifest.permission.CAMERA, 104);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        MenuInflater inflater = getMenuInflater();
        inflater.inflate(R.menu.menu_items, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();
        Context context = getApplicationContext();
        switch (id) {
            case R.id.premission_request:
                checkMultiPermission();
                Toast.makeText(context, "完成\n请多次点击申请权限\n直到不再弹窗询问权限", Toast.LENGTH_LONG).show();
                return true;
            case R.id.connect_server:
                startActivity(new Intent(MainActivity.this, ScannedBarcodeActivity.class));
                return true;
            case R.id.connect_server_manual:

                AlertDialog.Builder builder = new AlertDialog.Builder(this);
                builder.setTitle("输入二维码中的字符串");
                final EditText input = new EditText(this);
                input.setInputType(InputType.TYPE_CLASS_TEXT);
                builder.setView(input);
                builder.setPositiveButton("OK", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        String barcodes = input.getText().toString();
                        if (barcodes.startsWith("KrunkMsgServer://")){
                            String intentData = barcodes.substring(17);
                            String[] data = intentData.split("/-kbr-/");
                            Context context = getApplicationContext();
                            requestRegister(context,data[0],data[1]);
                            finish();
                        }else{
                            Context context = getApplicationContext();
                            Toast.makeText(context, "格式错误", Toast.LENGTH_LONG).show();
                        }
                    }
                });
                builder.setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        dialog.cancel();
                    }
                });
                builder.show();
                return true;
            case R.id.about:
                Toast.makeText(context, "KRUNK DESIGN\nhttps://krunk.cn\n\n此项目的作用为帮助多卡用户接收短信以及验证码而不需要随身携带所有的SIM卡", Toast.LENGTH_LONG).show();
                return true;
            case R.id.exit:
                stopService();
                finish();
                System.exit(0);
                return true;
            case R.id.keep:
                if(item.isChecked()){
                    item.setChecked(false);
                    stopService();
                    Toast.makeText(context, "已停止", Toast.LENGTH_LONG).show();
                }else{
                    item.setChecked(true);
                    startService();
                    Toast.makeText(context, "已启动", Toast.LENGTH_LONG).show();
                }
                return true;
            case R.id.info:
                String url = sharedPref.getString("MsgServer_url", "https://example/");
                //final String krunkkey = sharedPref.getString("MsgServer_key", "EmptyKey");
                Toast.makeText(context, "当前服务器: "+url, Toast.LENGTH_LONG).show();
                return true;
            default:
                return super.onOptionsItemSelected(item);
        }
    }

    @SuppressLint("SetTextI18n")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        connectionReceiver = new Receiver();
        intentFilter = new IntentFilter("android.provider.Telephony.SMS_RECEIVED");

        //检查权限
        checkMultiPermission();
        //开启前台服务
        startService();

        sharedPref = getSharedPreferences("MsgServer_connection", MODE_PRIVATE);
        //String url = sharedPref.getString("MsgServer_url", "https://example");
        //final String krunkkey = sharedPref.getString("MsgServer_key", "EmptyKey");

        String noticeText = sharedPref.getString("MsgServer_url", "0000");
        TextView messageField = (TextView) findViewById(R.id.message);
        TextView addressField = (TextView) findViewById(R.id.address);
        assert noticeText != null;
        if (noticeText.equals("0000")) {
            addressField.setText("点击右上角连接服务器");
            messageField.setText("第一次使用请多次点击右上角申请权限");
        }else{
            addressField.setText(noticeText);
        }

        Bundle extras = getIntent().getExtras();
        if (extras != null) {
            String address = extras.getString("MessageNumber");
            String message = extras.getString("Message");

            //TextView addressField = (TextView) findViewById(R.id.address);
            //TextView messageField = (TextView) findViewById(R.id.message);
            addressField.setText("来自 : " + address);
            messageField.setText("信息 : "+ message);

            Handler handler = new Handler();
            handler.postDelayed(new Runnable() {
                public void run() {
                    finish();
                }
            }, 10000);   //10 seconds
        }
    }

    public void startService() {
        Intent serviceIntent = new Intent(this, ForegroundService.class);
        serviceIntent.putExtra("inputExtra", "MsgServer is running");
        ContextCompat.startForegroundService(this, serviceIntent);
    }

    public void stopService() {
        Intent serviceIntent = new Intent(this, ForegroundService.class);
        stopService(serviceIntent);
    }

    @Override
    protected void onResume() {
        super.onResume();
        registerReceiver(connectionReceiver, intentFilter);

    }

    @Override
    protected void onDestroy() {
        super.onDestroy();

        unregisterReceiver(connectionReceiver);
    }

    public void requestRegister(final Context context, final String url, final String connectionkey){
        RequestQueue requestQueue;
        Cache cache = new DiskBasedCache(context.getCacheDir(), 1024 * 1024); // 1MB cap
        Network network = new BasicNetwork(new HurlStack());
        requestQueue = new RequestQueue(cache, network);
        requestQueue.start();

        StringRequest stringRequest = new StringRequest(Request.Method.POST, url+"register.php",
                new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        sharedPref.edit().putString("MsgServer_url", url).apply();
                        sharedPref.edit().putString("MsgServer_key", response).apply();
                        Toast.makeText(context, "绑定服务器成功"+"\n"+url+"\n"+response, Toast.LENGTH_LONG).show();
                    }
                }, new Response.ErrorListener() { //Create an error listener to handle errors appropriately.
            @Override
            public void onErrorResponse(VolleyError error) {
                //This code is executed if there is an error.
                Toast.makeText(context, "绑定服务器\n"+"传输失败\n"+error.toString(), Toast.LENGTH_LONG).show();
            }
        }){
            protected Map<String, String> getParams() {
                Map<String, String> MyData = new HashMap<String, String>();
                MyData.put("connectionkey", connectionkey);
                return MyData;
            }
        };
        // Add the request to the RequestQueue.
        requestQueue.add(stringRequest);
    }
}
