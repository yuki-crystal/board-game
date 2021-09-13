<?php
require_once('../db/connection.php');
require_once('../helper.php');
require_once('../error_code.php');

header('Content-Type: application/json');
set_CORS_response();

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    return;
}
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $live_questions = get_live_questions($_GET);
    echo json_encode([
        'status' => 'ok',
        'live_questions' => $live_questions
    ]);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $body = get_request_body();
    try {
        save_question($body);
    } catch (PDOException $e) {
        if ($e->errorInfo[0] == "23000") {
            echo json_encode(['status' => 'error', 'error' => "this question is already exist", "code" => $ERROR_CODE_question_DUPLICATE]);
            return;
        }
        http_response_code(500);
        echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        return;
    }

    echo json_encode(['status' => 'ok']);
} elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {
    $body = get_request_body();
    try {
        update_question($body);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        return;
    }

    echo json_encode(['status' => 'ok']);
} elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $body = get_request_body();
    try {
        delete_question($body);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
        return;
    }

    echo json_encode(['status' => 'ok']);
} elseif ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    set_CORS_response();
    return;
} else {
    $error =  'method not allowed';
    http_response_code(405);
    echo json_encode(['status' => 'error', 'error' => $error]);
    return;
};

function save_question($body)
{
    $conn = DBConnectionSingleton::get_connection();
    $handle = $conn->prepare('INSERT INTO live_question
    (classification,
	title,
    content,
    reply)
    VALUES
    (:classification,
    :title,
    :content,
    :reply);
	');
    $handle->bindParam(':classification', $body->classification, PDO::PARAM_STR);
    $handle->bindParam(':title', $body->title, PDO::PARAM_STR);
    $handle->bindParam(':content', $body->content, PDO::PARAM_STR);
    $handle->bindParam(':reply', $body->reply, PDO::PARAM_STR);
    $handle->execute();
}

function delete_question($body)
{
    $conn = DBConnectionSingleton::get_connection();
    $handle = $conn->prepare('DELETE FROM live_question WHERE id = :id');
    $handle->bindParam(':id', $body->id, PDO::PARAM_STR);
    $handle->execute();
}


function update_question($body)
{
    $conn = DBConnectionSingleton::get_connection();
    $handle = $conn->prepare('UPDATE live_question SET classification=:classification, title=:title, content=:content, reply=:reply WHERE id = :id');
    $handle->bindParam(':id', $body->id, PDO::PARAM_INT);
	$handle->bindParam(':classification', $body->classification, PDO::PARAM_STR);
	$handle->bindParam(':title', $body->title, PDO::PARAM_STR);
	$handle->bindParam(':content', $body->content, PDO::PARAM_STR);
	$handle->bindParam(':reply', $body->reply, PDO::PARAM_STR);
    $handle->execute();
}

function get_live_questions($parameters)
{
    $conn = DBConnectionSingleton::get_connection();
    $sql = 'SELECT * FROM live_question';

    $handle = $conn->prepare($sql . get_conditions($parameters, true));
    bind_sql_paras($handle, $parameters, true);
    $handle->execute();
    $result = $handle->fetchall(PDO::FETCH_OBJ);
    return $result;
}

function get_conditions($parameters, $skip_and_limit)
{
    $condition = '';

    if (array_key_exists('classification', $parameters)) {
        if (!$condition)
            $condition .= " WHERE ";
        else
            $condition .= " AND ";
        $condition .=  "classification = :classification";
    }
    if ($skip_and_limit) {
        if (array_key_exists('limit', $parameters)) {
            $condition .= sprintf(' LIMIT :limit');
        }
        if (array_key_exists('skip', $parameters)) {
            $condition .= sprintf(' OFFSET :skip');
        }
    }
    return $condition;
}
function bind_sql_paras($handle, $parameters, $skip_and_limit)
{
    $PARA_TYPE = [
        'skip' => PDO::PARAM_INT,
        'limit' => PDO::PARAM_INT,
        'classification' => PDO::PARAM_STR,
    ];
    foreach ($parameters as $key => $value) {
        if (!$skip_and_limit and in_array($key, ['skip', 'limit']))
            continue;
        if (!array_key_exists($key, $PARA_TYPE))
            continue;
        $handle->bindValue(sprintf(':%s', $key), $value,  $PARA_TYPE[$key]);
    }
}
?>