<?php

namespace App\Console\Commands\User;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update {email} {--password= : 最小6位}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update user password';


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
        $email=$this->argument('email');
        $password=array_get($this->options(),'password',null);
        $this->update($email,$password);
    }

    public function update($email,$password){
        $user=User::where('email','=',$email)->first();
        if($user){
            if($password){
                $user->password=app('hash')->make($password);
            }
            $user->update();
            echo "updated";
            
        }else{
            echo '用户不已存在';
        }
        
    }

    
}
