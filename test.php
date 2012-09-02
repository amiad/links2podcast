<?php

$page='http://hatul.info/l2pc/page.html';

require('links2podcast.php');
$l2pc=new PodcastCreatorFromLinks($page);
$l2pc->setTitle('test feed');
$l2pc->setDesc('test desc');
$l2pc->getFeed();