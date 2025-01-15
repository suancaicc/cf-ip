<?php
error_reporting(0);
$content = '';
// 获取IPv4数据
$content_v4 = getIpData();

// 获取IPv6数据
$content_v6 = getIpData('v6');

$content = $content_v4 . $content_v6;
echo ($content);
// 获取IP数据的函数
function getIpData($type = '')
{
    $data = ['key' => 'iDetkOys'];
    if ($type === 'v6') {
        $data['type'] = 'v6';
    }
    $json_data = curl($data);

    // 检查$json_data是否是一个数组
    if (is_array($json_data) && isset($json_data['info'])) {
        $content = '';
        // 遍历数组中的每个元素，提取ip和line字段并写入文件
        foreach ($json_data['info'] as $info) {
            //$txt .= $info['ip'] . "\n";

            // 根据 line 的值进行替换
            if ($info['line'] == 'CM') {
                if ($type === 'v6') {
                    $content .= $info['ip'] . '#移动v6_酸菜优选' . "\r\n";
                } else {
                    $content .= $info['ip'] . '#移动_酸菜优选' . "\r\n";
                }
            } elseif ($info['line'] == 'CU') {
                if ($type === 'v6') {
                    $content .= $info['ip'] . '#联通v6_酸菜优选' . "\r\n";
                } else {
                    $content .= $info['ip'] . '#联通_酸菜优选' . "\r\n";
                }

            } elseif ($info['line'] == 'CT') {
                if ($type === 'v6') {
                    $content .= $info['ip'] . '#电信v6_酸菜优选' . "\r\n";
                } else {
                    $content .= $info['ip'] . '#电信_酸菜优选' . "\r\n";
                }
            } else {
                $txt .= $txt . $info['ip'] . '#' . $info['ip'] . "<br>";
                $content .= $info['ip'] . '#' . $info['ip'] . "\r\n";
            }
        }
        return $content;
    } else {
        return "错误：从API获取的数据不是一个有效的数组。";
    }
}



// github提交使用示例
$token = ''; // 在这里放入你的 GitHub 令牌
$repo = 'suancaicc/cf-ip'; // 例如 'username/repo'
$branch = 'main'; // 你要提交的分支
$filePath = 'ip.txt'; // 要创建或更新的文件路径
//$content = "这是文件的内容"; // 文件内容
$commitMessage = "提交或更新文件"; // 提交的信息

// 调用函数
$git_post = githubPutFile($repo, $branch, $filePath, $content, $token, $commitMessage);
//exit($git_post);
// 将JSON字符串转换为PHP数组
$msg = json_decode($git_post, true);
// 获取commit的message
$message = $msg['commit']['message'];

// 输出响应
if ($message == "提交或更新文件") {
    echo "文件提交成功：" . $message;
} else {
    echo "文件提交失败，状态码：" . $msg['status'] . "</br>\r\n";
    echo "响应内容：$git_post";
}
/**
 * 将文件提交到GitHub仓库
 * 
 * @param string $repo GitHub仓库名称，格式为'username/repo'
 * @param string $branch 要提交的分支名称
 * @param string $filePath 要创建或更新的文件路径
 * @param string $content 要提交的文件内容
 * @param string $token GitHub API令牌
 * @param string $commitMessage 提交的信息
 */
function githubPutFile($repo, $branch, $filePath, $content, $token, $commitMessage)
{
    // 先获取文件的SHA值（如果文件已存在）
    $url = "https://api.github.com/repos/$repo/contents/$filePath?ref=$branch";
    $options = [
        'http' => [
            'header' => [
                "Authorization: token $token",
                "User-Agent: PHP-Script",
                "Accept: application/vnd.github.v3+json"
            ]
        ]
    ];

    // 创建HTTP上下文
    $context = stream_context_create($options);

    $response = file_get_contents($url, false, $context);

    $responseData = json_decode($response, true);

    // 获取文件的SHA值
    $file_sha = isset($responseData['sha']) ? $responseData['sha'] : '';

    // 将内容进行base64编码
    $base64_content = base64_encode($content);

    // 创建提交的JSON数据
    $data = [
        'message' => $commitMessage,
        'branch' => $branch,
        'content' => $base64_content,
    ];

    // 如果文件已存在，则加入SHA值
    if ($file_sha) {
        $data['sha'] = $file_sha;
    }

    // 提交文件
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/$repo/contents/$filePath");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: token $token",
        "User-Agent: PHP-Script",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // 执行请求
    $response = curl_exec($ch);
    curl_close($ch);
    // 输出响应
    return $response;
}

/**
 * 发送POST请求到指定URL并获取JSON数据
 * 
 * @param array $data 要发送的数据
 * @return array|null 返回解析后的JSON数据，如果出错则返回null
 */
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
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_data)
        )
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
