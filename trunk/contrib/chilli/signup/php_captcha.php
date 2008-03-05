<?php
session_start();

function randomAlphanumeric($length) {

    $chars = "abcdefghijkmnpqrstwxyz23456789ABCDEFGHKLMNPQRSTVWXYZ";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= ($length - 1)) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }

    return $pass;

}

	$ResultStr = randomAlphanumeric(5);

	$NewImage =imagecreatefromjpeg("captcha.jpg");			//image create by existing image and as back ground 

	$LineColor = imagecolorallocate($NewImage,233,239,239);		//line color 
	$TextColor = imagecolorallocate($NewImage, 255, 255, 255);	//text color-white

	imageline($NewImage,1,1,40,40,$LineColor);			//create line 1 on image 
	imageline($NewImage,1,100,60,0,$LineColor);			//create line 2 on image 

	imagestring($NewImage, 5, 20, 10, $ResultStr, $TextColor);	// Draw a random string horizontally 

	$_SESSION['key'] = $ResultStr;					// carry the data through session

	header("Content-type: image/jpeg");				// out out the image 

	imagejpeg($NewImage);						//Output image to browser 

?>
