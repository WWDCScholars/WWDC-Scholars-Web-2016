<?php
	/**
	 * Created by PhpStorm.
	 * User: Oliver
	 * Date: 08/05/2016
	 * Time: 12:37
	 */
	use phpHTML\UICore\UIDiv;
	use phpHTML\UICore\UIHeading;
	use phpHTML\UICore\UIImage;
	use phpHTML\UICore\UILink;
	use phpHTML\UICore\UIParagraph;

	require_once('phpUI/autoloader.php');
	require_once "server_conf.php";

	$id = isset($_GET['id'])?$_GET['id']:'';

	$scholar = json_decode(file_get_contents($API_URL . $id))[0];
	$info = $scholar->scholarsInfo;

	$embed = new UIDiv("<iframe width='100%' height='200' frameborder='0' style='border:0' src='https://www.google.com/maps/embed/v1/place?key={$GMAPS_KEY}&q={$info->location}' allowfullscreen></iframe>", 'map');

	$date_of_birth = new DateTime($info->birthday);
	$now = new DateTime();
	$age = $now->diff($date_of_birth)->format('%y');

	$batches = $scholar->batch;
	$batches_string = '';
	$twenty_sixteen = $batches[0];
	$latest_picture = $twenty_sixteen->profilePic;
	foreach($batches as $batch){
		if(!empty($batches_string)){
			$batches_string .= ', ';
		}
		$batches_string .= str_replace('WWDC', '', $batch->batchWWDC);
	}


	$scholar_view = new UIDiv([
		new UIDiv(
			new UIDiv([
				new UIImage($latest_picture, ['scholar_centered_photo']),
				new UIHeading(3, [$info->firstName, ' ', $info->lastName]),
				new UIHeading(5, $info->location)
			], ['col-xs-12']),
		'row'),
		new UIDiv([
			new UIDiv([
				new UIHeading(4, 'Age', 'red'),
				new UIHeading(4, $age)
			], 'col-xs-4'),
			new UIDiv([
				new UIHeading(4, 'Gender', 'green'),
				new UIHeading(4, ucfirst($info->gender))
			], 'col-xs-4'),
			new UIDiv([
				new UIHeading(4, 'Attended', 'blue'),
				new UIHeading(4, $batches_string)
			], 'col-xs-4')
		],'row'),
		new UIDiv([
			new UIDiv(new UIParagraph($info->shortBio), 'col-xs-12')
		], 'row')
	], ['scholar_detail', 'center']);



	$links_view = new UIDiv([], ['col-xs-12', 'links']);
	$links = ['itunes', 'github', 'facebook', 'twitter', 'website', 'linkedin', 'email'];
	$connect = $scholar->scholarConnect;
	foreach($links as $link){
		if(isset($connect->$link)){
			if($link == 'email'){
				$connect->$link = 'mailto:' . $connect->$link;
			}
			$links_view.= new UILink(new UIDiv('', [$link, 'link']), $connect->$link, '', '_blank');
		}
	}

	$submissions_scroll = new UIDiv([], ['submissions_scroll']);
	$submissions_view = new UIDiv([
		new UIHeading(3, 'Submissions'),
		$submissions_scroll
	], ['col-xs-12', 'submissions']);

	$screenshots = ['One', 'Two', 'Three', 'Four'];

	foreach($screenshots as $screenshot){
		$screenshot_var = 'screenshot' .$screenshot;
		if(isset($twenty_sixteen->$screenshot_var)){
			$submissions_scroll->addContent(new UIImage($twenty_sixteen->$screenshot_var));
		}
	}

	$content = $embed . $scholar_view . new UIDiv($links_view, ['row', 'center']) . new UIDiv($submissions_view, ['row', 'center']);

	require_once('placeholder_page.php');