<?php

it('returns a successful response', function () {
    // This app has no public landing page: '/' has redirected guests to
    // /login (and authenticated users to their role dashboard) since the
    // very first commit (6eb2e80) — see routes/web.php. There is no
    // regression here; this is Laravel's stock scaffold test, never updated
    // for this app's actual routing.
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
