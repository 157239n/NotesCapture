<?php
/*
$user_handle = "157239n";
if (!$session->has("user_handle")) {
    $session->set("user_handle", $user_handle);
    echo "Set user handle. Refresh to execute"; return;
}
$user = $userFactory->new($user_handle, "sos", "Quang", "America/New_York");
$rootCategory = $user->getRootCategory();
/**/

/*
$user_handle = "ynes";
if (!$session->has("user_handle")) {
    $session->set("user_handle", $user_handle);
    echo "Set user handle. Refresh to execute";
    return;
} else {
    if ($session->getCheck("user_handle") !== $user_handle) {
        $session->set("user_handle", $user_handle);
        echo "Modified user handle. Refresh to execute";
        return;
    }
}

$user = $userFactory->new($user_handle, "sos", "Ynes", "America/New_York");
$rootCategory = $user->getRootCategory();

$ml = $categoryFactory->new($rootCategory, "ML");
$cnn = $categoryFactory->new($ml, "CNN");
$rnn = $categoryFactory->new($ml, "RNN");
$lstm = $categoryFactory->new($rnn, "LSTM");
$google = $categoryFactory->new($rootCategory, "Google");
$servlet = $categoryFactory->new($google, "Servlet");
//$amherst = $categoryFactory->new($rootCategory, "Amherst");
//$cs = $categoryFactory->new($amherst, "CS");

$wBasics = $websiteFactory->new($ml, "http://blackbox.nn.157239n.com/basics");
$wAutograd = $websiteFactory->new($ml, "http://blackbox.nn.157239n.com/autograd");
$wConv = $websiteFactory->new($cnn, "http://blackbox.nn.157239n.com/conv");
$wConv2 = $websiteFactory->new($cnn, "http://blackbox.nn.157239n.com/conv-2");
$wScript = $websiteFactory->new($lstm, "https://github.com/hieudan225/deepLearning/blob/master/tv_script_generation/dlnd_tv_script_generation.ipynb");
//$wDog = $websiteFactory->new($cs, "https://github.com/hieudan225/deepLearning/tree/master/dog_project/dog_project");
/**/

//echo mime_content_type("https://youtube.com/yts/jsbin/www-pagead-id-vfl6fcGP0/www-pagead-id.js");
//readfile("https://youtube.com/yts/jsbin/www-pagead-id-vfl6fcGP0/www-pagead-id.js");


/*
// create curl resource
$ch = curl_init();

// set url
curl_setopt($ch, CURLOPT_URL, "example.com");

//return the transfer as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// $output contains the output string
$output = curl_exec($ch);

// close curl resource to free up system resources
curl_close($ch);
/**/

/*
$ch = curl_init("http://www.example.com/");
$fp = fopen("example_homepage.txt", "w");

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);

curl_exec($ch);
if(curl_error($ch)) {
    fwrite($fp, curl_error($ch));
}
curl_close($ch);
fclose($fp);
/**/


//echo "Something";
//var_dump($_SERVER);

//echo file_get_contents("https://www.tesla.com");
//echo file_get_contents("https://www.tesla.com/careers/job/autopilot-designverificationinternshipfall2020-56965");
//echo file_get_contents("https://domains.google.com");
//echo file_get_contents("http://blackbox.nn.157239n.com/basics");

//echo "abc";
