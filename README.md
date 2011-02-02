## Response Class

### Basic usage

	require '/path/to/modules/witty/witty.php';
	Witty::init();

	$response = Witty::instance('Response');
	$response->add_headers(array('foo' => 'bar'));
	$response->body = 'hello world';
	$response->check_etag();
	$response->send_headers();
	echo $response;
