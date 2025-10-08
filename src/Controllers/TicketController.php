<?php
declare(strict_types=1);

namespace App\Controllers;
use function App\{view,redirect,flash,auth_required,current_user,role_in,csrf_token,sanitize_filename,upload_dir,upload_url_base};
use App\Models\{Ticket,User};

class TicketController {
    public function dashboard(): void { auth_required(); $u=current_user(); $tickets=Ticket::forUser((int)$u['id'],$u['role']); view('tickets/index.php',['title'=>'Dashboard','tickets'=>$tickets]); }
    public function index(): void { $this->dashboard(); }
    public function create(): void { auth_required(); $agents=User::agents(); view('tickets/create.php',['title'=>'Create Ticket','agents'=>$agents]); }
    public function store(): void {
        auth_required(); $u=current_user();
        $title=trim($_POST['title']??''); $description=trim($_POST['description']??''); $priority=$_POST['priority']??'medium'; $category=trim($_POST['category']??''); $assignee_id=isset($_POST['assignee_id']) && $_POST['assignee_id']!=='' ? (int)$_POST['assignee_id'] : null;
        if($title===''||$description===''){ flash('danger','Title and description are required.'); redirect('/tickets/create'); }
        $id=Ticket::create(['title'=>$title,'description'=>$description,'priority'=>$priority,'category'=>$category,'requester_id'=>(int)$u['id'],'assignee_id'=>$assignee_id]);
        flash('success','Ticket created.'); redirect('/tickets/'.$id);
    }
    public function show(int $id): void {
        auth_required(); $u=current_user(); $ticket=Ticket::find($id); if(!$ticket){ http_response_code(404); echo 'Not found'; return; }
        if(!in_array($u['role'],['agent','admin'],true) && (int)$ticket['requester_id']!==(int)$u['id']){ http_response_code(403); echo 'Forbidden'; return; }
        $comments=Ticket::comments($id); $attachments=Ticket::attachments($id); $agents=User::agents();
        view('tickets/show.php',['title'=>'Ticket #'.$id,'ticket'=>$ticket,'comments'=>$comments,'attachments'=>$attachments,'agents'=>$agents]);
    }
    public function comment(int $id): void {
        auth_required(); $u=current_user(); $ticket=Ticket::find($id); if(!$ticket){ http_response_code(404); echo 'Not found'; return; }
        if(!in_array($u['role'],['agent','admin'],true) && (int)$ticket['requester_id']!==(int)$u['id']){ http_response_code(403); echo 'Forbidden'; return; }
        $body=trim($_POST['body']??''); if($body===''){ flash('danger','Comment cannot be empty'); redirect('/tickets/'.$id); }
        Ticket::addComment($id,(int)$u['id'],$body); flash('success','Comment added'); redirect('/tickets/'.$id);
    }
    public function updateStatus(int $id): void { auth_required(); if(!role_in(['agent','admin'])){ http_response_code(403); echo 'Forbidden'; return; } $status=$_POST['status']??'open'; Ticket::updateStatus($id,$status); flash('success','Status updated'); redirect('/tickets/'.$id); }
    public function assign(int $id): void { auth_required(); if(!role_in(['agent','admin'])){ http_response_code(403); echo 'Forbidden'; return; } $assignee_id=isset($_POST['assignee_id']) && $_POST['assignee_id']!=='' ? (int)$_POST['assignee_id'] : null; Ticket::assign($id,$assignee_id); flash('success','Assignee updated'); redirect('/tickets/'.$id); }
    public function attach(int $id): void {
        auth_required(); $u=current_user(); $ticket=Ticket::find($id); if(!$ticket){ http_response_code(404); echo 'Not found'; return; }
        if(!in_array($u['role'],['agent','admin'],true) && (int)$ticket['requester_id']!==(int)$u['id']){ http_response_code(403); echo 'Forbidden'; return; }
        if(!isset($_FILES['file']) || $_FILES['file']['error']!==UPLOAD_ERR_OK){ flash('danger','No file uploaded or upload error.'); redirect('/tickets/'.$id); }
        $file=$_FILES['file']; $max=(int)(getenv('MAX_UPLOAD_BYTES')?:5*1024*1024); if($file['size']>$max){ flash('danger','File too large.'); redirect('/tickets/'.$id); }
        $finfo=new \finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($file['tmp_name']); $allowed=['image/png'=>'png','image/jpeg'=>'jpg','image/gif'=>'gif','application/pdf'=>'pdf','text/plain'=>'txt']; if(!isset($allowed[$mime])){ flash('danger','Unsupported file type.'); redirect('/tickets/'.$id); }
        $ext=$allowed[$mime]; $original=sanitize_filename($file['name']); $random=bin2hex(random_bytes(16)).".$ext"; $destDir=upload_dir(); $dest=$destDir.'/'.$random; if(!move_uploaded_file($file['tmp_name'],$dest)){ flash('danger','Failed to save file.'); redirect('/tickets/'.$id); }
        Ticket::addAttachment($id,(int)$u['id'],$random,$original,$mime,(int)$file['size']); flash('success','Attachment uploaded.'); redirect('/tickets/'.$id);
    }
}
