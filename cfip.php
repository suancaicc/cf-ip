<?php
// 要发送的数据
$data = array('key' => 'iDetkOys');

// 发送POST请求并获取JSON数据
$json_data = curl($data);

// 检查$json_data是否是一个数组
if (is_array($json_data) && isset($json_data['info'])) {
    // 打开一个文件用于写入
    $file = fopen('cfip.txt', 'w');

    // 遍历数组中的每个元素，提取ip和line字段并写入文件
    foreach ($json_data['info'] as $info) {
        //$txt .= $info['ip'] . "\n";

    // 根据 line 的值进行替换
    if ($info['line'] == 'CM') {
        $txt = $txt.$info['ip'] . '#移动' . "<br>";
        $output = $info['ip'] . '#移动' . "\n";
    } elseif ($info['line'] == 'CU') {
        $txt =$txt. $info['ip'] . '#联通' . "<br>" ;
        $output = $info['ip'] . '#联通' . "\n";
    }elseif ($info['line'] == 'CT') {
        $txt = $txt.$info['ip'] . '#电信' . "<br>" ;
        $output = $info['ip'] . '#电信' . "\n";
    } else {
        //echo $info['line'] . PHP_EOL . "\n"; // 如果没有匹配，上输出原值
        //$txt = $info['ip'] . '#' . $info['ip'] . PHP_EOL . "\n";
    }
    fwrite($file, $output);
}
exit($txt);
echo $txt;

    // 关闭文件
    fclose($file);
} else {
    echo "错误：从API获取的数据不是一个有效的数组。";
}

function curl($data)
{
    // 将数据转换为JSON格式
    $json_data = json_encode($data);

    // 初始化cURL会话
    $ch = curl_init('https://api.hostmonit.com/get_optimization_ip');

    // 设置cURL选项
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json_data))
    );

    // 执行cURL会话并获取响应
    $response = curl_exec($ch);

    // 检查是否有错误发生
    if (curl_errno($ch)) {
        echo 'Curl error: ' . curl_error($ch);
        return null; // 返回null以表示错误
    }

    // 关闭cURL会话
    curl_close($ch);

    // 将响应解码为数组
    $decoded_data = json_decode($response, true);

    // 检查解码是否成功
    if ($decoded_data === null) {
        echo "错误：无法解析API响应为JSON格式。";
        return null; // 返回null以表示错误
    }

    return $decoded_data;
}
?>
