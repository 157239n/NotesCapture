<?php

/**
 * Old proof-of-concept way to get the remote resource and dumps it out.
 */

readfile(base64_decode($requestData->getCheck("rss")));
