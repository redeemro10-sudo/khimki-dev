<?php

status_header((int) http_response_code() === 410 ? 410 : 404);

echo view('404', app('sage.data'))->render();
