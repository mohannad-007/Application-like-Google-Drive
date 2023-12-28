<?php

namespace App\Http\Middleware;

use App\Models\File;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFileOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $fileId=$request->file_id;
        $file=file::find($fileId);
        if($file->user_id!=auth()->user()->id){
            return response()->json(['message'=>'You are not the owner of this file'],403);
        }
        return $next($request);
    }
}
