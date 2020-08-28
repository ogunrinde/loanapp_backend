<?php

namespace App\Http\Middleware;

use Closure;
use App\userroles;
use App\permissions;
use Illuminate\Support\Facades\Route;

class UserPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$doc)
    {
        $doc_type = explode(":",$doc)[0];
        $access_type = explode(":",$doc)[1];
        $role = $this->getuserRole($request);

        if($role == null)
        {
            return response(['status' => 'success', "error" => "UnAuthorized"]);
        }    
        
        $permission = $this->getPermission($role->role_id);

        if($permission == null)
        {
            return response(['status' => 'success', "error" => "UnAuthorized"]);
        }

        $status = $this->Access($doc_type,$access_type,$permission);

        if($status == "0")
        {
            return response(['status' => 'failed', 'error' => 'Not Authorized']);
        }

       
        
        return $next($request);
    }

    public function getuserRole($request)
    {
        $role = userroles::where(['user_id' => $request->user()->id])->first();
        return $role;
    }

    public function getPermission($id)
    {
        $permission = permissions::where(['role_id' => $id])->get();
        return $permission;
    }

    public function Access($doc_type,$access_type, $permission)
    {
        foreach($permission as $perm)
        {
            if ($perm->doc_type == $doc_type)
            {
               if($access_type == 'create')
               {
                 return $perm->create;
               }
               else if($access_type == 'edit')
               {
                return $perm->edit;
               }
               else if($access_type == 'approval')
               {
                return $perm->approval;
               }
               else if($access_type == 'deactivate')
               {
                return $perm->deactivate;
               }
               else if($access_type == 'view')
               {
                return $perm->view;
               }
               else if($access_type == 'cancel')
               {
                return $perm->cancel;
               }
            }
        }

        return 0;
    }
}
