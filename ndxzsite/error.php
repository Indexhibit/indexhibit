<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
<title><?php echo $rs['obj_name']; ?></title>
<style type='text/css'>
* { margin: 0; padding: 0; }
body { font: 11px Arial; margin: 24px; padding: 0; }
h1 { margin-bottom: 6px; padding: 0; font-family: Arial, sans-serif; font-weight: bold; font-size: 11px; }
p { width: 400px; }
</style>
</head>
<body>	
<h1><?php echo $rs['obj_name']; ?></h1>
<p><?php echo $rs['error_message']; ?></p>
</body>
</html>