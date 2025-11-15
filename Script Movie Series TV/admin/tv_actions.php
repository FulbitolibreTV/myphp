<?php
require_once '../config.php';
if(!check_session()){header('Location: login.php');exit;}

$tv_file='../data/tv_channels.json';
$tv_channels=file_exists($tv_file)?json_decode(file_get_contents($tv_file),true):[];

$action=$_GET['action']??'';

switch($action){
    case 'add':
        $tv_channels[]=[
            'name'=>$_GET['name'],
            'category'=>$_GET['category'],
            'image'=>$_GET['image'],
            'url'=>$_GET['url']
        ];
        break;
    case 'edit':
        $index=(int)$_GET['index'];
        if(isset($tv_channels[$index])){
            $tv_channels[$index]=[
                'name'=>$_GET['name'],
                'category'=>$_GET['category'],
                'image'=>$_GET['image'],
                'url'=>$_GET['url']
            ];
        }
        break;
    case 'delete':
        $index=(int)$_GET['index'];
        if(isset($tv_channels[$index])){
            array_splice($tv_channels,$index,1);
        }
        break;
}

file_put_contents($tv_file,json_encode($tv_channels,JSON_PRETTY_PRINT));
header('Location: detv.php');
exit;
