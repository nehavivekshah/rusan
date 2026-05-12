<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Companies;
use App\Models\Roles;

$u = User::where('email', 'info@rusanpharma.com')->first();
if ($u) {
    echo "User: " . $u->email . "\n";
    echo "CID: " . $u->cid . "\n";
    echo "ROLE: " . $u->role . "\n";
    
    $c = Companies::find($u->cid);
    echo "Company: " . ($c ? $c->name : 'NULL') . "\n";
    
    $r = Roles::find($u->role);
    echo "Role: " . ($r ? $r->title : 'NULL') . "\n";
} else {
    echo "User not found\n";
}
