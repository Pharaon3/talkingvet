use Illuminate\Support\Facades\DB;
 
$users = DB::table('logintokens')->get();
 
foreach ($logintokens as $logintoken) {
    echo $logintoken->token;
}
