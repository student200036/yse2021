<?php
/* 
【機能】
書籍の出荷数を指定する。確定ボタンを押すことで確認画面へ出荷個数を引き継いで遷移す
る。

【エラー一覧（エラー表示：発生条件）】
このフィールドを入力して下さい(吹き出し)：出荷個数が未入力
出荷する個数が在庫数を超えています：出荷したい個数が在庫数を超えている
数値以外が入力されています：入力された値に数字以外の文字が含まれている
*/
/*
 * ①session_status()の結果が「PHP_SESSION_NONE」と一致するか判定する。
 * 一致した場合はif文の中に入る。
 */
if (session_status() == PHP_SESSION_NONE) {
	//②セッションを開始する
	session_start();
}

//③SESSIONの「login」フラグがfalseか判定する。「login」フラグがfalseの場合はif文の中に入る。
if (!$_SESSION['login']){
	//④SESSIONの「error2」に「ログインしてください」と設定する。
	$_SESSION['error2'] ="ログインしてください";
	//⑤ログイン画面へ遷移する。
	header('Location: login.php');
}

//⑥データベースへ接続し、接続情報を変数に保存する
$db_name = 'zaiko2021_yse';
$db_host = 'localhost';
$db_port = '3306';
$db_user = 'zaiko2021_yse';
$db_password = '2021zaiko';
$dsn = "mysql:dbname={$db_name};host={$db_host};charset=utf8;port={$db_port}";

try {
	$pdo = new PDO($dsn,$db_user,$db_password);
	$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
} catch (PDOException $e) {
	echo "接続失敗: ".$e->getMessage();
	exit;
}

//⑦データベースで使用する文字コードを「UTF8」にする

//⑧POSTの「books」の値が空か判定する。空の場合はif文の中に入る。
if(empty($_POST['books'])){
	//⑨SESSIONの「success」に「出荷する商品が選択されていません」と設定する。
	$_SESSION['success'] = "出荷する商品が選択されていません";
	//⑩在庫一覧画面へ遷移する。
	header('Location: zaiko_ichiran.php');
}

function getId($id,$con){

	//⑪書籍を取得するSQLを作成する実行する。
	$sql ="SELECT * FROM books WHERE id = :id";
	// その際にWHERE句でメソッドの引数の$idに一致する書籍のみ取得する。
	// SQLの実行結果を変数に保存する。
	$stmt = $con->prepare($sql);
	$stmt->execute(['id' => $id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	//⑫実行した結果から1レコード取得し、returnで値を返す。
	return $result;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>出荷</title>
<link rel="stylesheet" href="css/ichiran.css" type="text/css" />
</head>
<body>
<!-- ヘッダ -->
<div id="header">
	<h1>出荷</h1>
</div>

<!-- メニュー -->
<div id="menu">
	<nav>
		<ul>
			<li><a href="zaiko_ichiran.php?page=1">書籍一覧</a></li>
		</ul>
	</nav>
</div>

<form action="syukka_kakunin.php" method="post">
	<div id="pagebody">
		<!-- エラーメッセージ -->
		<div id="error">
		<?php
		/*
		 * ⑬SESSIONの「error」にメッセージが設定されているかを判定する。
		 * 設定されていた場合はif文の中に入る。
		 */ 
		if(isset($_SESSION['error'])){
			//⑭SESSIONの「error」の中身を表示する。
			echo $_SESSION['error'];
		}
		?>
		</div>
		<div id="center">
			<table>
				<thead>
					<tr>
						<th id="id">ID</th>
						<th id="book_name">書籍名</th>
						<th id="author">著者名</th>
						<th id="salesDate">発売日</th>
						<th id="itemPrice">金額(円)</th>
						<th id="stock">在庫数</th>
						<th id="in">出荷数</th>
					</tr>
				</thead>
				<?php 
				/*
				 * ⑮POSTの「books」から一つずつ値を取り出し、変数に保存する。
				 */
				foreach ($_POST['books'] as $books) {
					$book = getId($books,$pdo);
					// ⑯「getId」関数を呼び出し、変数に戻り値を入れる。その際引数に⑮の処理で取得した値と⑥のDBの接続情報を渡す。
					
				?>
				<input type="hidden" value="<?php echo $book['id']	/* ⑰ ⑯の戻り値からidを取り出し、設定する */;?>" name="books[]">
				<tr>
					<td><?php echo $book['id'] /* ⑱ ⑯の戻り値からidを取り出し、表示する */;?></td>
					<td><?php echo $book['title']	/* ⑲ ⑯の戻り値からtitleを取り出し、表示する */;?></td>
					<td><?php echo $book['author']	/* ⑳ ⑯の戻り値からauthorを取り出し、表示する */;?></td>
					<td><?php echo $book['salesDate']	/* ㉑ ⑯の戻り値からsalesDateを取り出し、表示する */;?></td>
					<td><?php echo $book['price']	/* ㉒ ⑯の戻り値からpriceを取り出し、表示する */;?></td>
					<td><?php echo $book['stock']	/* ㉓ ⑯の戻り値からstockを取り出し、表示する */;?></td>
					<td><input type='text' name='stock[]' size='5' maxlength='11' required></td>
				</tr>
				<?php
				}
				?>
			</table>
			<button type="submit" id="kakutei" formmethod="POST" name="decision" value="1">確定</button>
		</div>
	</div>
</form>
<!-- フッター -->
<div id="footer">
	<footer>株式会社アクロイト</footer>
</div>
</body>
</html>
