<?php

namespace App\Http\Middleware;

use App\Models\File;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FileReserved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
//        $hasParameterMemberId=$request->has('member_id');
//        if ($hasParameterMemberId)
//        {
//            $memberId=$request->member_id;
//            // dd($memberId);
////            $files = File::where('group_id', $request->group_id)
////                ->where('is_reserved', true)
////                ->where('is_active', true)
////                ->with('fileEvents')
////                ->whereHas('fileEvents', function ($query) use ($memberId) {
////                    $query->where('event_type_id', 4)
////                        ->orWhere('event_type_id', 5)
////                        ->where('user_id', $memberId);
////                })
////                ->orderBy('fileEvents.date', 'desc')
////                ->get();
//            $files = File::whereGroupID($request->group_id)
//                ->whereIsReserved(true)
//                ->whereIsActive(true)
//                ->join('file_events', 'files.id', '=', 'file_events.file_id')
//                ->whereEventTypeID(4)
//                ->whereEventTypeID(5)
//                ->whereUserID($memberId)
//                ->orderBy('file_events.date', 'desc')
//                ->get();
//
//            dd($files);
//        }
        $hasParameterGroupId = $request->has('group_id');
        //dd($hasParameter);
        if($hasParameterGroupId)
        {
            $groupId = $request->group_id;
            $file = File::where('group_id', $groupId)->where('is_reserved', 1)->first();
            if ($file)
            {
                return response()->json(['message' => 'file is reserved'], 403);
            }
            else
                return $next($request);
        }
        $hasParameterFileId=$request->has('file_id');
       // dd($hasParameterFileId);
        if($hasParameterFileId)
        {
        $fileId=$request->file_id;
        $file=File::where('id',$fileId)->where('is_reserved',1)->first();
              if($file)
                 {
                      return response()->json(['message' => 'file is reserved'], 403);
                 }
              else
                  return $next($request);
        }

        else
        {
            $hasParametersIds = $request->all();
            $isReserved = false;
            if ($hasParametersIds) {
                $count = count($hasParametersIds);
                for ($i = 1; $i <= $count; $i++) {
                    $id = $hasParametersIds['id' . $i];
                    $file = File::where('id', $id)->where('is_reserved', 1)->first();
                    if ($file)
                        $isReserved = true;
                }
                if ($isReserved) {
                    return response()->json(['message' => 'files is reserved'], 403);
                }
            }
        }


        return $next($request);
    }
}
