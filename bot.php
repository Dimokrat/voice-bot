<?php


const TOKEN = '5420937989:AAG7PZ5RjeNZeVP076MYDULhZGU089PjNbw';

//https://api.telegram.org/bot5420937989:AAG7PZ5RjeNZeVP076MYDULhZGU089PjNbw/setWebhook?url=https://1c77-217-79-29-247.eu.ngrok.io/bot.php

$data = json_decode(file_get_contents('php://input'), TRUE);
file_put_contents('bot_loggs.txt', '$data: '.print_r($data, 1)."\n", FILE_APPEND);

$message = mb_strtolower($data['message']['text']);
$chat_id = $data['message']['chat']['id'];

if (!empty($data['message']['text'])){
    if ($data['message']['text'] == '/start'){
        $method = 'sendMessage';
        $send_data = [
            'text' => 'Привет! Я бот для перевода голосовых сообщений в текст',
            'chat_id' => $data['message']['chat']['id']
        ];
        sendTelegram($method, $send_data);
        $send_data['text'] = 'Могу перевести голосовое сообщение в текст =)';
        sendTelegram($method, $send_data);
    } else {
        $method = 'sendMessage';
        $send_data = [
            'text' => 'Запиши мне голосовое и я переведу его в текст',
            'chat_id' => $data['message']['chat']['id']
        ];
        sendTelegram($method, $send_data);
    }
}

if (!empty($data['message']['voice'])){
    $send_data['file_id'] = $data['message']['voice']['file_id'];
    getVoice($send_data);
    $voice_text = getVoiceToText();
    if($voice_text == "Маша дома") {
        getTextToVoice("Маш+уля, наконец то ты дома! Рады тебя видеть. Не обижайся и не дуйся. Дима тебя очень любит! чмок чмок чмок");
    }
    $method = 'sendMessage';
    $send_data = [
        'text'=> $voice_text,
        'chat_id'=>$data['message']['chat']['id'],
        'reply_to_message_id' => $data['message']['message_id']
    ];

    sendTelegram($method, $send_data);
    exit('ok');
}

function sendTelegram($method, $send_data){
    $ch = curl_init('https://api.telegram.org/bot' . TOKEN . '/' . $method);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    file_put_contents('resdhfs.txt', '$res: '.print_r($send_data, 1)."\n", FILE_APPEND);
    return $result;
}

function getVoice($send_data){                  //Получение файла голосовухи в папку с ботом
    $file_path = getPath($send_data);
    file_put_contents('res.txt', '$array_path: '.print_r($file_path, 1)."\n", FILE_APPEND);
    $url = 'https://api.telegram.org/file/bot' . TOKEN . '/' . $file_path;
    $ext = explode(".",$file_path);
    $name_our_file = 'voice'.".".$ext[1];
    return file_put_contents($name_our_file, file_get_contents($url));
}

function getPath($send_data){       // Получение пути файла на сервере телеги
    $temp_array_path = json_decode(sendTelegram('getFile', $send_data), true);

    return $temp_array_path['result']['file_path'];
}

function getVoiceToText(){
    $api_key = 'AQVN0WycR8R8D2JGJvMICAhwfI4E_ih2VN2-Qg9E';
    $id_catalog = 'b1gs2ckn18qp544cvgvf';
    $audioFileName = "voice.oga";

    $file = fopen($audioFileName, 'rb');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://stt.api.cloud.yandex.net/speech/v1/stt:recognize?lang=ru-RU&folderId="."$id_catalog"."&format=oggopus");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Api-Key ' . $api_key, 'Transfer-Encoding: chunked'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

    curl_setopt($ch, CURLOPT_INFILE, $file);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($audioFileName));
    $res = curl_exec($ch);
    curl_close($ch);
    $decodedResponse = json_decode($res, true);
    if (isset($decodedResponse["result"])) {
        echo $decodedResponse["result"];
    } else {
        echo "Error code: " . $decodedResponse["error_code"] . "\r\n";
        echo "Error message: " . $decodedResponse["error_message"] . "\r\n";
    }

    fclose($file);
    $response = $decodedResponse['result'];
    return $response;
}

function getTextToVoice($voice_text){
    $f = file_get_contents("https://tts.voicetech.yandex.net/generate?text=".urlencode($voice_text)."&format=mp3&sampleRateHertz=64000&lang=ru-RU&speed=1&speaker=omazh&emotion=good&key=abfbe0a3-819a-4a81-806a-1d9abfe007f6");
    file_put_contents(__DIR__."/f.mp3",$f);
    exec("D:/app/open server/openserver/domains/test-bot.local/f.mp3");
}

function getNone(){
    // функция ничего не делает
    // нужна для теста
}
