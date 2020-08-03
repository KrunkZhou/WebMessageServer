package cn.krunk.msgserver;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.telephony.SmsMessage;
import android.widget.Toast;

import java.util.Date;
import java.util.Objects;
import java.util.Calendar;

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

public class Receiver extends BroadcastReceiver {

    private static final String SMS_RECEIVED = "android.provider.Telephony.SMS_RECEIVED";
    SharedPreferences sharedPref;

    public void sendToServer(final Context context, final String number, final String msg, final String time){
        RequestQueue requestQueue;
        // Instantiate the cache
        Cache cache = new DiskBasedCache(context.getCacheDir(), 1024 * 1024); // 1MB cap
        // Set up the network to use HttpURLConnection as the HTTP client.
        Network network = new BasicNetwork(new HurlStack());
        // Instantiate the RequestQueue with the cache and network.
        requestQueue = new RequestQueue(cache, network);
        // Start the queue
        requestQueue.start();

        sharedPref = context.getSharedPreferences("MsgServer_connection", Context.MODE_PRIVATE);
        String url = sharedPref.getString("MsgServer_url", "https://example/");
        final String krunkkey = sharedPref.getString("MsgServer_key", "EmptyKey");

        StringRequest stringRequest = new StringRequest(Request.Method.POST, url+"new.php",
                new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        //The String 'response' contains the server's response.
                        String result;
                        if (response.equals("1")){
                            result="成功发送到服务器";
                        }else{
                            result="发生错误\n"+response.toString();
                        }
                        Toast.makeText(context, "短信服务器\n" + number + "\n" + msg+ "\n"+result, Toast.LENGTH_LONG).show();
                    }
                }, new Response.ErrorListener() { //Create an error listener to handle errors appropriately.
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        //This code is executed if there is an error.
                        Toast.makeText(context, "短信服务器\n" + number + "\n" + msg+ "\n"+"传输失败\n"+error.toString(), Toast.LENGTH_LONG).show();
                    }
                }){
                    protected Map<String, String> getParams() {
                        Map<String, String> MyData = new HashMap<String, String>();
                        MyData.put("krunkkey", krunkkey);
                        MyData.put("number", number);
                        MyData.put("msg", msg);
                        MyData.put("time", time);
                        return MyData;
                    }
                };
        // Add the request to the RequestQueue.
        requestQueue.add(stringRequest);
    }

    @Override
    public void onReceive(Context context, Intent intent) {

        if(Objects.equals(intent.getAction(), SMS_RECEIVED)) {

            Bundle pudsBundle = intent.getExtras();
            SmsMessage[] msgs = null;
            StringBuilder str = new StringBuilder();
            String msg_from = "";

            if (pudsBundle != null) {

                Object[] pdus = (Object[]) pudsBundle.get("pdus");

                assert pdus != null;
                msgs = new SmsMessage[pdus.length];

                for (int i=0; i<msgs.length; i++){
                    msgs[i] = SmsMessage.createFromPdu((byte[])pdus[i]);
                    str.append(msgs[i].getMessageBody());
                    msg_from = msgs[i].getOriginatingAddress();
                }

                Intent smsIntent = new Intent(context, MainActivity.class);

                smsIntent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK);

                smsIntent.putExtra("MessageNumber", msg_from);

                smsIntent.putExtra("Message", str.toString());

                context.startActivity(smsIntent);

                Date currentTime = Calendar.getInstance().getTime();
//                Toast.makeText(context, "短信服务器\n" + msg_from + "\n" +
//                        str.toString()+ "\n"+currentTime.toString(), Toast.LENGTH_LONG).show();

                sendToServer(context,msg_from,str.toString(),currentTime.toString()); //发送到服务器

            }
        }else {
            Toast.makeText(context, "Forgot", Toast.LENGTH_SHORT).show();
        }

    }
}
