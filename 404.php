<?php

status_header((int) http_response_code() === 410 ? 410 : 404);

echo view(app('sage.view'), app('sage.data'))->render();
