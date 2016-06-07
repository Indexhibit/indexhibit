<?php if (!defined('LIBPATH')) exit('No direct script access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title><?php echo $this->title; ?></title>

<?php echo $this->tpl_css(); ?>
<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie.css" /><![endif]-->
<?php echo $this->tpl_js(); ?>

<?php echo $this->tpl_add_script(); ?>

</head>

<body>

	<!-- BODY BEGIN -->
	
	<?php echo $this->body; ?>
	
	<!-- BODY END -->


</body>
</html>