<?php if (!defined('SITE')) exit('No direct script access allowed.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title><?php echo $this->title; ?></title>

<?php echo $this->tpl_css(); ?>
<!--[if IE 6]><link rel="stylesheet" type="text/css" href="asset/css/ie.css" /><![endif]-->
<?php echo $this->tpl_js(); ?>

<?php echo $this->tpl_add_script(); ?>

</head>

<body>

<?php echo $this->tpl_update_available(); ?>

<div id='all'>
	
<div id='top'><!-- --></div>
	
<div id='header' class='c2'>
	<div class='col'><?php echo $this->tpl_site_menu(); ?></div>
	<div class='col right'><?php echo $this->tpl_prefs(); ?></div>	
	<div class='cl'><!-- --></div>	
</div>

<div id='main'>
	
	<div id='location' class='c2'>
		<div class='col'>
			<h2><?php echo $this->tpl_location(); ?><?php echo $this->tpl_action(); ?></h2>
		</div>
		<div class='col right'>
			<?php echo $this->tpl_sub_location(); ?>
		</div>
		<div class='cl'><!-- --></div>		
	</div>

	<!-- BODY BEGIN -->
	<form name='mform' action='' method='post' <?php echo $this->tpl_form_type(); ?><?php echo $this->tpl_form_onsubmit(); ?>>
	<?php echo $this->body; ?>
	</form>
	
	<!-- BODY END -->

	<div class='cl'><!-- --></div>

</div>

	<div id='footer' class='c2'>
		<div class='col'><?php echo $this->tpl_foot_left(); ?></div>
		<div class='col right'><?php echo $this->tpl_foot_right(); ?></div>
		<div class='cl'><!-- --></div>
	</div>

	<?php echo $this->tpl_speed(); ?>
</div>
</body>
</html>