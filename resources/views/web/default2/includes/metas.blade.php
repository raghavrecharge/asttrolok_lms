<meta charset="utf-8">

<meta name="csrf-token" content="{{ csrf_token() }}">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
@if (isset($robots) and !empty($robots))
<meta name='robots' content="'follow,'{{$robots ?? ""}}">
@else
<meta name='robots' content="{{ $pageRobot ?? 'follow, index' }}">
@endif
@if (isset($pageDescription) and !empty($pageDescription))
    <meta name="description" content="{{ $pageDescription }}">

@endif
<link rel='shortcut icon' type='image/x-icon' href="{{ url(!empty($generalSettings['fav_icon']) ? $generalSettings['fav_icon'] : 'https://storage.googleapis.com/astrolok/webp/store/1/logo121.webp') }}">
<link rel="manifest" href="/mix-manifest.json?v=4">
<meta name="theme-color" content="#FFF">

<meta name="msapplication-starturl" content="/">
<meta name="msapplication-TileColor" content="#FFF">
<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">

<meta name="apple-mobile-web-app-title" content="{{ !empty($generalSettings['site_name']) ? $generalSettings['site_name'] : 'https://storage.googleapis.com/astrolok/webp/store/1/logo121.webp' }}">
<link rel="apple-touch-icon" href="{{ url(!empty($generalSettings['fav_icon']) ? $generalSettings['fav_icon'] : '') }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">

<link rel='icon' href='{{ url(!empty($generalSettings['fav_icon']) ? $generalSettings['fav_icon'] : '') }}'>
<meta name="application-name" content="{{ !empty($generalSettings['site_name']) ? $generalSettings['site_name'] : 'https://storage.googleapis.com/astrolok/webp/store/1/logo121.webp' }}">
<meta name="mobile-web-app-capable" content="yes">

<meta name="layoutmode" content="fitscreen/standard">
<link rel="home" href="{{ url('') }}">

<meta property="og:type" content="website">
<meta property="og:locale" content="en_US" />
<meta property="og:locale:alternate" content="en_GB" />
<meta property="og:locale:alternate" content="en_IN" />
<meta property="og:site:name" content="Asttrolok">
<meta property="og:title" content="{{ $pageTitle ?? '' }}">
@if (isset($pageDescription) and !empty($pageDescription))
<meta property="og:description" content="{{ (!empty($ogDescription)) ? $ogDescription : $pageDescription }}">
@endif
<script >
    setTimeout(function() {
    var headID = document.getElementsByTagName("head")[0];
    var newScript = document.createElement('script');
    newScript.type = 'text/javascript';
    newScript.src = '//code.jivosite.com/widget/0vDO9nN5Jy';
    headID.appendChild(newScript);
}, 20000);

</script>
<?php
$main_url =  $_SERVER['REQUEST_URI'];

$url_array=explode("/",$main_url);
?>
<meta property="og:url" content="https://www.asttrolok.com{{$main_url}}">
<?php
//	echo $main_url;
if(isset($url_array[2])){
if($url_array[1]=="blog")
{
?>
<meta property="og:image" content="{{ url(!empty($post->image) ? config('app.img_dynamic_url') . $post->image : '') }}">
<?php }else{	?>
<meta property="og:image" content="https://storage.googleapis.com/astrolok/store/1/Home/Asttrolok-Logo.png">
<?php }}
else{
    ?>
    <meta property="og:image" content="https://storage.googleapis.com/astrolok/store/1/Home/Asttrolok-Logo.png">
    <?php
}
?>

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@Asttrolok">
<meta name="twitter:title" content="{{ $pageTitle ?? '' }}">
 @if (isset($pageDescription) and !empty($pageDescription))
<meta property="twitter:description" content="{{ (!empty($ogDescription)) ? $ogDescription : $pageDescription }}">
@endif
	<?php
//	echo $main_url;
if(isset($url_array[2])){
if($url_array[1]=="blog")
{
?>
<meta name="twitter:image" content="{{ url(!empty($post->image) ? config('app.img_dynamic_url') . $post->image : '') }}">
<?php }else{	?>
<meta name="twitter:image" content="https://storage.googleapis.com/astrolok/store/1/Home/Asttrolok-Logo.png">
<?php }}
else{
?>
<meta name="twitter:image" content="https://storage.googleapis.com/astrolok/store/1/Home/Asttrolok-Logo.png">
<?php
}?>

{!! getSeoMetas('extra_meta_tags') !!}

<?php

$main_url =  $_SERVER['REQUEST_URI'];

	$url_array=explode("/",$main_url);
 //	echo $main_url;
if(isset($url_array[1])){
	if($url_array[1]=="instructors")
	{	?>
 <meta name="keywords" content="consult with an Astrologer, Astrology in Hindi, Horoscope, Kundli Bhagya, Kundli, Kundli match, Zodiac Signs, match-making horoscope, matchmaking marriage, Jyotish, Talk to Astrologer, Consultation vedic astrologer, Prediction for your future, Online Astrology Predictions by Best Astrologer">
<?php 	}
	if($url_array[1]=="classes?sort=newest"){
	?>
	<meta name="keywords" content="Learn Astrology, learn astrology online, Learn Basic to Advance Vedic Astrology, learn vedic astrology in hindi, learn plamistry, learn vastu , Learn Palmistry Course Online, School of astrology, Free Astrology Course, online astrology course , live astrology course , learn astrology with certification, astrology learning course, Vedic Astrology with a modern touch, online platform for Vedic astrology, learn astrology online free, free astrology tutorials online, consultation with astrologer, Vedic Science Courses">
	<?php 	}

	if($url_array[1]=="contact"){
	?>
	<meta name="keywords" content="Contact Asttrolok, Reach Asttrolok, Contact Information Asttrolok, Get in Touch with Asttrolok, Asttrolok Contact Details, Contact Us at Asttrolok, Asttrolok Contact Page, Asttrolok Customer Support, Contact Asttrolok Online, Asttrolok Phone Number, Asttrolok Email Address">
	<?php 	}

	}
	if($main_url=="/"){	?>
	<meta name="keywords" content="learn astrology online, institute of vedic astrology, Certified astrology course, learn plamistry , Learn vastu, Learn numrology, best astrologer , online vedic astrology, vedic astrology course, vedic indian astrology course, best book to learn vedic astrology, best vedic astrology books for beginners, distance learning course on vedic astrology, learn vedic astrology, learn vedic astrology in hindi, learn vedic astrology online, learning vedic astrology step by step, online vedic astrology certification, online vedic astrology course in english, vedic astrology books, vedic astrology courses in india, vedic astrology for beginners, vedic astrology online course, how to learn astrology, hindu astrology, online astrology courses, is astrology a science, aastrology classes online, astrology certificate courses">

	<?php
	}
	?>
