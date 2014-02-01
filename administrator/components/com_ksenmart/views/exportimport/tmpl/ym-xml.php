<?php
chdir(dirname(__FILE__).'/../../../../../../');
include('configuration.php');
$config=new JConfig();
mysql_connect($config->host,$config->user,$config->password);
mysql_select_db($config->db);
mysql_query('set names utf8');
$currencies='';
$categories='';	
$offers='';
$Itemid='';
$query="select id from {$config->dbprefix}menu where link='index.php?option=com_ksenmart&view=shopcatalog' and published='1' limit 1";
$res=mysql_query($query);
if (mysql_num_rows($res)>0)
	$Itemid='&amp;Itemid='.mysql_result($res,0,'id');
$query="select value from {$config->dbprefix}ksenmart_settings where name='version'";
$res=mysql_query($query);
$version=mysql_result($res,0,'value');
$query="select value from {$config->dbprefix}ksenmart_yandeximport where setting='shopname'";
$res=mysql_query($query);
$shopname=mysql_result($res,0,'value');
$query="select value from {$config->dbprefix}ksenmart_yandeximport where setting='company'";
$res=mysql_query($query);
$company=mysql_result($res,0,'value');
$query="select * from {$config->dbprefix}ksenmart_currencies where code='RUR'";
$res=mysql_query($query);
$rur_rate=mysql_result($res,0,'rate');
$query="select * from {$config->dbprefix}ksenmart_currencies";
$res=mysql_query($query);
while($row=mysql_fetch_array($res))
	$currencies.='<currency id="'.$row['code'].'" rate="'.round($rur_rate/$row['rate'],4).'"/>';
$query="select value from {$config->dbprefix}ksenmart_yandeximport where setting='categories'";
$res=mysql_query($query);
$cats=mysql_result($res,0,'value');
$cats=json_decode($cats,true);
$query="select * from {$config->dbprefix}ksenmart_categories where id in (".implode(',',$cats).")";
$res=mysql_query($query);
while($row=mysql_fetch_array($res))
	$categories.='<category id="'.$row['id'].'" '.($row['parent']!=0?'parentId="'.$row['parent'].'"':'').'>'.$row['title'].'</category>';	
$cats_where="pc.category_id in (".implode(',',$cats).")";
$query="select p.*,pc.category_id,(select filename from {$config->dbprefix}ksenmart_files where owner_id=p.id and owner_type='product' and media_type='image' order by ordering limit 1) as picture,(select title from {$config->dbprefix}ksenmart_manufacturers where id=p.manufacturer) as manufacturer_name,(select code from {$config->dbprefix}ksenmart_currencies where id=p.price_type) as code from {$config->dbprefix}ksenmart_products as p inner join {$config->dbprefix}ksenmart_products_categories as pc on pc.product_id=p.id where p.published='1' and type='product' and ($cats_where) group by p.id";
$res=mysql_query($query);
while($row=mysql_fetch_array($res))
{
	if ($row['picture']!='')
		$row['picture']='http://'.$_SERVER['HTTP_HOST'].'/media/ksenmart/images/products/original/'.$row['picture'];
	else	
		$row['picture']='http://'.$_SERVER['HTTP_HOST'].'/media/ksenmart/images/products/original/no.jpg';
	$offers.='<offer id="'.$row['id'].'" available="true" bid="1">
		<url>http://'.$_SERVER['HTTP_HOST'].'/index.php?option=com_ksenmart&amp;view=shopproduct&amp;id='.$row['id'].':'.$row['alias'].$Itemid.'</url>
		<price>'.$row['price'].'</price>
		<currencyId>'.$row['code'].'</currencyId>
		<categoryId>'.$row['category_id'].'</categoryId>
		<picture>'.$row['picture'].'</picture>	
		<delivery>true</delivery>
		<name>'.htmlspecialchars($row['title'],ENT_QUOTES).'</name>
		<vendor>'.htmlspecialchars($row['manufacturer_name'],ENT_QUOTES).'</vendor>	
		<description>'.htmlspecialchars($row['content'],ENT_QUOTES).'</description>
	</offer>';
}	
mysql_close();
header('Content-Type: text/xml;charset:utf-8');
echo '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
<yml_catalog date="'.date('Y-m-d H:i').'">
<shop>
	<name>'.$shopname.'</name>
	<company>'.$company.'</company>
	<url>'.$_SERVER['SERVER_NAME'].'</url>
	<platform>KsenMart based on Joomla</platform>
	<version>'.$version.'</version>
	<agency>L.D.M. Co</agency>
	<email>boss.dm@gmail.com</email>	
	<currencies>
		'.$currencies.'
	</currencies>
	<categories>
		'.$categories.'
	</categories>
	<offers>
		'.$offers.'
	</offers>
</shop>
</yml_catalog>
';