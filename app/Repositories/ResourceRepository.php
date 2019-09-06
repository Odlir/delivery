<?php

namespace App\Repositories;
use App\Models\Resource;
use App\Models\ResourceFolder;
use App\Models\RoleUser;
use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;

class ResourceRepository
{
    public function create(Request $request)
    {
        $errors = Resource::validate($request, [
            'resource' => 'required|file',
            'folder' => 'required|exists:resource_folders,name',
            'owner' => 'required|in:restaurant,product,user',
            'restaurant_id' => 'required_if:owner,restaurant|exists:restaurants,id',
            'product_id' => 'required_if:owner,product|exists:products,id',
            'role_id' => 'required_if:owner,user|exists:roles,id',
            'user_id' => 'required_if:owner,user|exists:users,id'
        ]);

        //limitar acceso a carpetas

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $resourceFolder = ResourceFolder::where('name', $request->folder)->first();

        $resource = new Resource();
        $resource->resource_folder_id = $resourceFolder->id;

        $fileName = '';

        if($request->owner == 'restaurant')
        {
            $fileName .= $request->restaurant_id;
            $resource->restaurant_id = $request->restaurant_id;
        }
        elseif($request->owner == 'product')
        {
            $fileName .= $request->product_id;
            $resource->product_id = $request->product_id;
        }
        else{
            $roleUser = RoleUser::where('role_id', $request->role_id)->where('user_id', $request->user_id)->first();

            if($roleUser == null)
                throw new \Exception('The user is not associated to the given role', 2);

            $fileName .= $roleUser->role_id.'_'.$roleUser->user_id;

            $resource->role_user_role_id = $request->role_id;
            $resource->role_user_user_id = $request->user_id;
        }

        $fileName .= Uuid::generate().'.'.$request->file('resource')->getClientOriginalExtension();;

        if($request->file("resource")->move($resourceFolder->name, $fileName) == null)
            throw new \Exception('An error has occurred while saving the file', 3);

        $resource->path = $resourceFolder->name.'/'.$fileName;
        
        $resource->save();

        return $resource;
    }

    public function get(Request $request)
    {
        return null;
    }

}