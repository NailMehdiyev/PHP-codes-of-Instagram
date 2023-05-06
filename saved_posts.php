<?php
require("constants.php");
require("db.php");
require("function.php");

$method=$_SERVER["REQUEST_METHOD"];
$responce=[];

$code=200;


if(isset($_SERVER['PHP_AUTH_USER'])){

    if($_SERVER['PHP_AUTH_USER']==AUTH_USER && $_SERVER['PHP_AUTH_PW']==AUTH_PW){

        if($method=="GET"){
            if(isset($_GET['userid']) && !empty(trim($_GET['userid']))){

                $userid=$_GET['userid'];

                if(getUserModel($db,$userid)!=0){


                    $savedposts=$db->prepare("SELECT * FROM savedposts WHERE userid=:userid ORDER BY postid DESC");
                    $savedposts->execute(array(":userid"=>$userid));

                                               if($savedposts->rowCount()>0){
                                                $savedposts=$savedposts->fetchAll(PDO::FETCH_ASSOC);

                                                $responce['code']=$code=200;
                                                $responce['message']=httpStatus($code);

                                                $responce['posts']=[];

                                               
                                               for($i=0;$i<count($savedposts);$i++){
                                                $savedposts[$i]=getPostModel($db,$savedposts[$i]['postid']);

                                                array_push($responce['posts'], $savedposts[$i]) ;                                 $responce['posts']=[];
                                               
                                               
                                               }


                }else{

                    $responce['code']=$code=404;
                    $responce['message']=ERROR_NOT_FOUND;

                }
            }else{
                $responce['code']=$code=404;
                $responce['message']=ERROR_NOT_FOUND_USER;

            }
        }else{
            $responce['code']=$code=400;
            $responce['message']=httpStatus($code);
        }
    }elseif($method=="POST"){

        if(isset($_POST['userid'])  && !empty($_POST['userid']) && 
        isset($_POST['postid'])  && !empty(trim($_POST['postid']))){
           $postid=$_POST['postid'];
           $userid=$_POST['userid'];


           if(getUserModel($db,$userid)!=0){

            if(getPostModel($db,$postid)!=0){

                $savedposts=$db->prepare("SELECT * FROM savedposts WHERE userid=:userid AND postid=:postid ");
                $savedposts->execute(array(":userid"=>$userid,":postid"=>$postid));



                                           if($savedposts->rowCount()==0){

                                            $insertsavedposts=$db->prepare("INSERT INTO savedposts(userid,postid) VALUES(userid=:userid,postid=:postid)");
                                            $insertsavedposts->execute(array(":userid"=>$userid,":postid"=>$postid));
                                           



                                           if($insertsavedposts->rowCount()>0){

                                            $responce['code']=$code=200;
                                            $responce['message']=INFO_INSERTED;
                                           }else{

                                            $responce['code']=$code=400;
                                            $responce['message']=ERROR_MESSAGE;
                                        }

            }else{

                    $responce['code']=$code=400;
                    $responce['message']=ERROR_ALREADY_SAVED;
                }
            
           }else{

            $responce['code']=$code=404;
            $responce['message']=ERROR_NOT_FOUND_POST;
        }



        }else{

            $responce['code']=$code=404;
            $responce['message']=ERROR_NOT_FOUND_USER;
        }
    }else{
        $responce['code']=$code=400;
        $responce['message']=httpStatus($code);

    }
}elseif($method=="DELETE"){

    if(isset($_GET['userid'])  && !empty(trim($_GET['userid'])) && 
    isset($_GET['postid'])  && !empty(trim($_GET['postid']))){
       $postid=$_GET['postid'];
       $userid=$_GET['userid'];

       $unsaveposts=$db->prepare("DELETE FROM savedposts WHERE userid=:userid AND
       postid=:postid");
       $unsaveposts->execute(array(":userid"=>$userid,":postid"=>$postid));


if($unsaveposts->rowCount()>0){

    $responce['code']=$code=200;
    $responce['message']=INFO_DELETED;
}else{

    $responce['code']=$code=400;
    $responce['message']=ERROR_MESSAGE;
}
}else{
$responce['code']=$code=400;
$responce['message']=httpStatus($code);
}


}else{
$responce['code']=$code=405;
$responce['message']=httpStatus($code);
}

}else{
$responce['code']=$code=401;
$responce['message']=httpStatus($code);
}

}else{
$responce['code']=$code=401;
$responce['message']=httpStatus($code);
}

setHeader($code);
echo json_encode($responce);

?>

