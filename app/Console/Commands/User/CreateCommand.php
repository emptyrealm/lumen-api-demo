<?php

namespace App\Console\Commands\User;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {--name= : 名称} {--email= : 邮箱} {--password= : 最小6位} {--scopes?= : 权限,用","分割}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create user';





    /**
     * Create a new command instance.
     * PartSnapshots constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name=array_get($this->options(),'name',null);
        $email=array_get($this->options(),'email',null);
        $password=array_get($this->options(),'password',null);
        $scopes=array_get($this->options(),'scopes',null);
        $this->create($name,$email,$password,$scopes);

    }

    public function create($name,$email,$password,$scopes){
        $curUser=DB::table('users')->where('email','=',$email)->first();
        if(!$curUser){
            if($email && $password){
                $user=new User();
                $user->name=$name;
                $user->email=$email;
                $user->password=app('hash')->make($password);;
                $user->scopes=$scopes;
                $user->save();
                echo "created";
            }else{
                echo 'email 和 password 必须存在';
            }
            
        }else{
            echo '已存在';
        }
        
    }

    
}
