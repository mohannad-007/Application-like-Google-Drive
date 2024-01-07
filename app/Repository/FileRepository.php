<?php

namespace App\Repository;
use App\Models\EventType;
use App\Models\File;
use App\Models\FileEvent;
use App\Models\FileUserReserved;
use App\Models\User;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\callback;

class FileRepository implements  FileRepositoryInterface
{
   protected $fileModel;
   protected $userModel;
   protected $groupModel;
   protected $fileEventModel;
   protected $eventTypeModel;
   public  function __construct(File $fileModel,User $userModel,Group $groupModel,FileEvent $fileEventModel,EventType $eventTypeModel)
   {
       $this->fileModel=$fileModel;
       $this->userModel=$userModel;
       $this->groupModel=$groupModel;
       $this->fileEventModel=$fileEventModel;
       $this->eventTypeModel=$eventTypeModel;

   }
    public function uploadFileToGroup($data):?File
    {
        $groupName=$this->groupModel->where('id',$data['group_id'])->first()->name;
       // dd($groupName);
        $file=$data['file'];
        $fileName=$file->getClientOriginalName();
        $basename = pathinfo($fileName, PATHINFO_FILENAME);
       // dd($basename);
        $fileNameWithoutExtension=pathinfo($fileName, PATHINFO_FILENAME);
       // dd($basename);
        $fileExtension=$file->getClientOriginalExtension();
       // dd($fileExtension);
        if (!$this->checkFileIfExist($data['group_id'],$fileNameWithoutExtension,$fileExtension))
        {
            $exist=Storage::disk('local')->exists($groupName.'/'.$fileName);
            if(!$exist) {
                //Store File in Local Disk in the folder with group name
                Storage::disk('local')->put($groupName . '/' . $fileName, file_get_contents($file), [
                    'overwrite' => false,
                ]);
                $fileUrl = Storage::disk('local')->url($groupName . '/' . $fileName);
                $this->fileModel->name = $fileNameWithoutExtension;
                $this->fileModel->extension = $fileExtension;
                $this->fileModel->group_id = $data['group_id'];
                $this->fileModel->user_id = $data['user_id'];
                $this->fileModel->is_active = true;
                $this->fileModel->is_reserved = false;
                $this->fileModel->path = $fileUrl;
                $this->fileModel->save();
                return $this->fileModel;
            }else
                return null;

        }
        else
        {
            return null;
        }
    }
    public function updateFileAfterCheckOut($data):?File
    {
        $file=$data['file'];
        $fileName=$file->getClientOriginalName();
      //  dd($fileName);
        $basename = pathinfo($fileName, PATHINFO_FILENAME);
       // dd($basename);
        $fileExtension=$file->getClientOriginalExtension();
       // dd($fileExtension);
        $fileDb=$this->fileModel->where('id',$data['file_id'])->where('name',$basename)->where('extension',$fileExtension)->where('is_active',1)->first();

        if($fileDb)
        {
            $groupName=$this->groupModel->where('id',$fileDb->group_id)->first()->name;
            $exist=Storage::disk('local')->exists($groupName . '/' . $fileName);
          // dd($exist);
            if ($exist)
            {
                $result=Storage::disk('local')->put($groupName . '/' . $fileName, file_get_contents($file), [
                    'overwrite' => true,
                ]);
               // dd($result);
                if($result)
                {
                    return $fileDb;
                }
                else
                {
                    return null;
                }
            }else
            {
                return null;
            }


        }else
        {
            return null;
        }
    }
    public function checkFileIfExist($group_id,$file_name,$file_extension):bool
    {
        return $this->fileModel->where('group_id',$group_id)->where('name',$file_name)->where('extension',$file_extension)->where('is_active',1)->exists();
    }
    public function addFileEvent($file_id,$user_id,$event_type_id)
    {
        $fileEventModel= new FileEvent();
        $fileEventModel->file_id=$file_id;
        $fileEventModel->event_type_id=$event_type_id;
        $fileEventModel->user_id=$user_id;
        $fileEventModel->date=Carbon::now();
        $fileEventModel->save();
        if ($fileEventModel)
            return $fileEventModel;
        else
            return null;


    }
    public function downloadFile($data): ?string
    {
//        $fileUrl = $this->fileModel->where('id', $data['file_id'])->first()->path;
//        // dd($fileUrl);
//        $fileName = basename($fileUrl);
//        //dd($fileName);
////        $path=$fileUrl->path;
//        $fileContent = Storage::disk('local')->url($fileUrl);
////         dd($fileContent);
//        $mimeType = Storage::disk('local')->mimeType($fileUrl);
//        $headers = [
//            'Content-Type' => $mimeType,
//            'Content-Disposition' => "attachment; filename={$fileName}",
//        ];
//
//        $responseData = [
//            'content' => $fileContent,
//            'headers' => $headers,
//        ];
//        //dd($responseData);
//
//
//        return([
//            $responseData,
//            $fileUrl
//        ])  ;
        $file = $this->fileModel->where('id', $data['file_id'])->first();

        if (!$file) {
            return null; // أو يمكنك التعامل مع حالة عدم وجود الملف هنا
        }

        $fileUrl = $file->path;
        $fileUrl = Storage::disk('local')->url($fileUrl); // الحصول على الرابط العام للملف

        return $fileUrl;
    }
    public function deleteFile($data):bool
    {
        $result= $this->fileModel->where('id',$data['file_id'])->where('user_id',$data['user_id'])->update(['is_active'=>0]);
        $file=$this->fileModel->where('id',$data['file_id'])->where('user_id',$data['user_id'])->first();
        $path=$file->path;
        if ($file->group_id==1)
        {
            $path='public/'.$file->name.'.'.$file->extension;
            //dd($path);
        }
        $pathToTrash='trash/'.$file->name.'.'.$file->extension;
       // dd($pathToTrash);
        $isDone=Storage::move($path, $pathToTrash);
       // dd($isDone);

//        $f=$this->fileModel->find($data['file_id']);
//        $file=$data['file_id']->find();
        return $result;

    }
    public function checkIn($data):bool
    {
        $result= $this->fileModel->where('id',$data['file_id'])->where('is_active',1)->lockForUpdate()->update(['is_reserved'=>1]);
        return $result;
    }
    public function checkOut($data): bool
    {
        $result= $this->fileModel->where('id',$data['file_id'])->where('is_active',1)->update(['is_reserved'=>0]);
        return $result;
    }
    public function bulkCheckIn($data): bool
    {
        $count=count($data);
        $isReserved=false;
        for($i=1;$i<=$count;$i++)
        {
            $id=$data['id'.$i];
            $result= $this->fileModel->where('id',$id)->where('is_active',1)->lockForUpdate()->update(['is_reserved'=>1]);
            if ($result)
                $isReserved=true;
            else
                $isReserved=false;
        }
        return $isReserved;
    }
    public function showReport()
    {
        $fileEvents = FileEvent::with('file','user','eventType')->get();
        return response()->json(['file_events' => $fileEvents], 200);
    }

    public function showReportForFile($data)
    {
        $fileEvents = FileEvent::where('file_id',$data['file_id'])->with('file','user','eventType')->get();
        return $fileEvents;
    }

}
