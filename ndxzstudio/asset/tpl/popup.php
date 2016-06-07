<?php if (!defined('LIBPATH')) exit('No direct script access allowed.');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); ?>
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

<body style='background: transparent;'>

<div style='background-color: #fff; -moz-border-radius: 4px; -webkit-border-radius: 4px; border-radius: 4px; padding-bottom: 18px;'>
<div id='p-header' style='-moz-border-radius-topleft: 4px; -webkit-border-top-left-radius: 4px; border-top-left-radius: 4px; -moz-border-radius-topright: 4px; -webkit-border-top-right-radius: 4px; border-top-right-radius: 4px;'>
	<div id='p-loc'><?php echo $this->pop_location; ?></div>
	<div id='p-links'><?php echo $this->tpl_pop_links(); ?></div>
	<div class='cl'><!-- --></div>
</div>

<div id='p-container'>
	<div id='p-content'>
		<form id='mformpop' name='mformpop' action='<?php echo $this->tpl_form_action(); ?>' method='post' <?php echo $this->tpl_form_type(); ?>>
		<?php echo $this->body; ?>
		</form>
	</div>
</div>
</div>

</body>
</html>