<?php

namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Course;
use App\Entity\Video;
use App\Entity\Like;
use App\Entity\Notification;
use App\Entity\Rates;
use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\LoginManagerAuthenticator; 
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Cache\CacheItem;
use App\Form\CourseFormType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\Query\Expr;
use Knp\Component\Pager\PaginatorInterface;


final class MainController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em){
        $this->em=$em;
    }
    #[Route('/index', name: 'app_main')]
    public function index(): Response
    {
        $notifications=null;
        $couseReposetory = $this->em->getRepository(Course::class);

        $course = $couseReposetory->findBy([] , ['rating' => 'DESC'] , 6);
        $user=$this->getUser();
        if($user instanceof User){
            $notifications=$user->getNotifications();
            return $this->render('index.html.twig' , ['courses' =>$course , 'notifications' => $notifications]);
        }
        return $this->render('index.html.twig' , ['courses' =>$course , 'notifications' => $notifications]);
      
    }

    #[Route('/profile' , name:'app_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profilePage(): Response{
        $user = $this->getUser();
        if($user instanceof User){
            $notification = $user->getNotifications();
        }
        return$this->render('/profile/profile.html.twig' , ['notifications' => $notification]);
    }

    #[Route('/updatePicture', name: 'edit_profile_picture')]
    public function updatePicture(Request $request): Response
    {
        $user = $this->getUser();
        if ($user instanceof User) {

            $pic = $request->files->get('profile_picture');

            if ($pic instanceof UploadedFile) {
                $newname = uniqid() . '.' . $pic->guessExtension();
                $user->setImagepath($newname);
                $pic->move($this->getParameter('kernel.project_dir') . '/public/images', $newname);

                $this->em->persist($user); 
                $this->em->flush(); 

            }
        }
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('admin_profile');
        }

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/updateProfileInfo' , name : 'app_update_profileInfo')]
    public function updateProfilr(Request $request , UserAuthenticatorInterface $authenticator, LoginManagerAuthenticator $formAuthenticator , UserPasswordHasherInterface $userPasswordHasher): Response{
        $user = $this->getUser();
        $fullname=$request->request->get('fullname');
        $password=$request->request->get('password');
        
        if($user instanceof User){

            $user->setFullname($fullname);
            $user->setPassword($userPasswordHasher->hashPassword($user, $password));
            
            $this->em->persist($user);
            $this->em->flush();
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('admin_profile');
        }
        
        return $authenticator->authenticateUser($user, $formAuthenticator, $request);
    }

    #[Route('/UpdateEmail', name: 'app_update_email')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]

    public function updateEmail(Request $request, MailerInterface $mailer, CacheInterface $cache): Response
    {
        $user = $this->getUser();
        if($user instanceof User){
            $newEmail = $request->request->get('email');
        if(!$newEmail){
            echo "email ùmakiwslch";
            exit;
        }

        // Generate a unique token
        $token = Uuid::v4()->toRfc4122();

        // Store email in cache with expiration time (e.g., 1 hour)
        $cache->get("email_update_{$token}", function (CacheItem $item) use ($newEmail) {
            $item->expiresAfter(3600); // 1 hour
            return $newEmail;
        });

        // Generate verification link (with only the token)
        $verificationUrl = $this->generateUrl(
            'app_verify',
            ['token' => $token ,
            'userId' => $user->getId()
        ],
            
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Send verification email
        $email = (new TemplatedEmail())
            ->from('uthmanjunaid400@gmail.com')
            ->to($newEmail)
            ->subject('Verify Your New Email Address')
            ->context(['verificationUrl' => $verificationUrl])
            ->htmlTemplate('emails/verify_email.html.twig' , ['verificationUrl' => $verificationUrl ]);
            

        $mailer->send($email);

        $this->addFlash('success', 'A verification email has been sent.');
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('admin_profile');
        }
        $user = $this->getUser();
        $repository = $this->em->getRepository(Notification::class);
        $notifications = $repository->findBy(['users'=>$user]);
        

        return $this->render('profile/profile.html.twig' , ['notifications'=>$notifications]);
    }

    #[Route('/verify-email/{token}/{userId}', name: 'app_verify')]
    public function verifyEmail(string $token, CacheInterface $cache, EntityManagerInterface $em ,int $userId): Response
    {
        $newEmail = $cache->get("email_update_{$token}", fn () => null);
        if (!$newEmail) {
            echo "Cache Miss or Expired: " . $token;
            exit;
        }

        $user = $em->getRepository(User::class)->find($userId);
        if ($user instanceof User) {
            $user->setEmail($newEmail);
            $em->flush();

            // Clear the token from cache
            $cache->delete($token);

            // Add flash message for success
            $this->addFlash('success', 'Your email has been updated successfully! Please log in again.');

            // Redirect to login page
            return $this->redirectToRoute('app_login'); 
        }

        // If user is not found
        throw new \Exception('User not found');
    }

    #[Route('/addCourse' ,name : 'app_add_Course')]
    public function addCourse(Request $request){

        if($request->isMethod('POST')){
            $course= new Course;
            $course->setTitle($request->request->get('title'));
            $course->setLanguage($request->request->get('language'));
            $course->setCategory($request->request->get('category'));
            $course->setDescription($request->request->get('description'));
            $course->setStatus('teaching');

            $user=$this->getUser();
            $course->setUsers($user);

            $oldimagepath=$request->files->get('course_image');

            if($oldimagepath instanceof UploadedFile){

                $newname=uniqid().'.'.$oldimagepath->guessExtension();
                $course->setCourseImage($newname);
                $oldimagepath->move($this->getParameter('kernel.project_dir') . '/public/images', $newname);

            }

            $this->em->persist($course);
            $this->em->flush();

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('course/addCourse.html.twig');

    }

    #[Route('/editCourse/{id}' , name : 'app_editCourse')]
    public function editCourse(Course $course){

        $user = $this->getUser();
        if($user instanceof User){
            $notifications = $user->getNotifications();
        }

        return $this->render('course/editCourse.html.twig' , ['course' => $course , 'notifications'=>$notifications]);
    }

    #[Route('/updateCourse/{id}' , name :'app_updateCourseInfos' , methods: ['POST'])]
    public function updateCourse(Request $request , Course $course , int $id){

        $title=$request->request->get('title');
        $language=$request->request->get('language');
        $category=$request->request->get('category');
        $description=$request->request->get('description');
        $course_image=$request->files->get('course_image');

        if($title){
            $course->setTitle($title);
        }
        if($language){
            $course->setLanguage($language);
        }
        if($category){
            $course->setCategory($category);
        }
        if($description){
            $course->setDescription($description);
        }
        if($course_image instanceof UploadedFile){
            $newImage=uniqid().'.'.$course_image->guessExtension();
            $course->setCourseImage($newImage);
            $course_image->move($this->getParameter('kernel.project_dir') . '/public/images', $newImage);
        }

        $this->em->persist($course);
        $this->em->flush();

        return $this->redirectToRoute('app_editCourse' , ['id'=> $course->getId()]);
    }
    #[Route('/deleteCourse/{id}/{user_id?}' , name : 'app_deleteCourse' , defaults: ['user_id' => 0]) ]
    public function deleteCourse(Course $course , Request $request , int $user_id){

        //dd($user_id);

        $this->em->remove($course);
        $this->em->flush();

        if(in_array('ROLE_ADMIN' , $this->getUser()->getRoles())){
            return $this->redirectToRoute('seeMore' , ['id' => $user_id]);
        }

        return $this->redirectToRoute('app_profile');
    }

    #[Route('/addVideo/{id}' , name:'app_addVideo')]
    public function appVideo(Request $request ,int $id){

        $repository=$this->em->getRepository(Course::class);
        $course=$repository->findOneBy(['id'=>$id]);

        if($request->isMethod('POST')){
            $video = new Video();
            $video->setTitle($request->request->get('title'));
            $video->setDescription($request->request->get('description'));
            $video->setCourses($course);

            $oldVideoName=$request->files->get('video');
            $oldImageName=$request->files->get('course_image');

            if($oldVideoName instanceof UploadedFile && $oldImageName instanceof UploadedFile){

                $newVideoName = uniqid().'.'.$oldVideoName->guessExtension();
                $video->setVideoPath($newVideoName);
                $oldVideoName->move($this->getParameter('kernel.project_dir') . '/public/videos', $newVideoName);

                $newname=uniqid().'.'.$oldImageName->guessExtension();
                $video->setVideoImage($newname);
                $oldImageName->move($this->getParameter('kernel.project_dir') . '/public/images', $newname);
            }
            $this->em->persist($video);
            $this->em->flush();

            return $this->redirectToRoute('app_editCourse' , [ 'id' => $course->getId() ]);
 
        }
        return $this->render('video/addVideo.html.twig' , ['id_course' => $course->getId()]);
    }

    #[Route('/updateVideo/{id}' , name:'app_updateVideo')]
    public function updateVideo(Request $request ,Video $video){

        if($request->isMethod('POST')){
            $title=$request->request->get('title');
            $description = $request->request->get('description');

            if($title){
                $video->setTitle($title);
            }
            if($description){
                $video->setDescription($description);
            }
            
            $oldVideoName=$request->files->get('video');
            $oldImageName=$request->files->get('course_image');

            if($oldVideoName && $oldVideoName instanceof UploadedFile){
                $newVideoName = uniqid().'.'.$oldVideoName->guessExtension();
                $video->setVideoPath($newVideoName);
                $oldVideoName->move($this->getParameter('kernel.project_dir') . '/public/videos', $newVideoName);
            }

            if($oldImageName && $oldImageName instanceof UploadedFile){
                $newname=uniqid().'.'.$oldImageName->guessExtension();
                $video->setVideoImage($newname);
                $oldImageName->move($this->getParameter('kernel.project_dir') . '/public/images', $newname);
            }

            $this->em->persist($video);
            $this->em->flush();

            return $this->redirectToRoute('app_editCourse' , [ 'id' => $video->getCourses()->getId() ]);
 
        }
        return $this->render('video/updateVideo.html.twig' , ['video' => $video]);
    }
    

    #[Route('/Video/{id}' , name: 'app_video')]
    public function displayVideoe(int $id){
        $repository=$this->em->getRepository(Video::class);
        $video = $repository->findOneBy(['id'=>$id]);
        $user = $this->getUser();
        if($user instanceof User){
            $notifications = $user->getNotifications();
        }
        return $this->render('Video/display.html.twig' , ['video' => $video , 'notifications'=>$notifications]);
    }

    #[Route('/like/{id}' , name:'app_like')]
    public function like(Video $video){
        $user=$this->getUser();

        $repository=$this->em->getRepository(Like::class);

        if($user instanceof User ){
            $like = $repository->findOneby(['user'=> $user , 'Video'=>$video]);
            if($like){
                $this->em->remove($like);
                $this->em->flush();
            }
            else{
                $newLike=new Like();
                $newLike->setVideo($video);
                $newLike->setUser($user);
                $this->em->persist($newLike);
                $this->em->flush();

                if($user != $video->getCourses()->getUsers()){
                    $notification = new Notification();
                    $notification->setContent("You have a new like from ".$user->getFullname()." in ".$video->getCourses()->getTitle()."/".$video->getTitle());
                    $notification->setUsers($video->getCourses()->getUsers());
                    $notification->setSender($user);
                    $this->em->persist($notification);
                    $this->em->flush();
                }
            }
        }

        return $this->redirectToRoute('app_video', ['id' => $video->getId()]);
    }

    #[Route('/comment/{id}' , name : 'app_comment')]
    public function addComment(Video $video , Request $request){

        $user = $this->getUser();
        $comment_text = $request->request->get('comment');

        $comment = new Comment();

        $comment->setContent($comment_text);
        $comment->setUsers($user);
        $comment->setVideos($video);

        $this->em->persist($comment);
        $this->em->flush();

        if($user != $video->getCourses()->getUsers()){
            if($user instanceof User){
                $notification = new Notification();
                $notification->setContent("You have a new Comment from ".$user->getFullname()." in ".$video->getCourses()->getTitle()."/".$video->getTitle().".");
                $notification->setUsers($video->getCourses()->getUsers());
                $notification->setSender($user);
                $this->em->persist($notification);
                $this->em->flush();
            }
        }

        return $this->redirectToRoute('app_video', ['id' => $video->getId()]);

    }

    #[Route('/deleteVideo/{id}' , name:'app_deletVideo')]
    public function deleteVideo(Video $video){
        $this->em->remove($video);
        $this->em->flush();
        
        return $this->redirectToRoute('app_editCourse' , [ 'id' => $video->getCourses()->getId() ]);
    }

    #[Route('/rate/{id}/{index}' , name: 'app_rate')]
    public function rate(Request $request , Course $course , int $index=0){
        $user = $this->getUser();
        $rating = $request->request->get('rating');

        $Raterepository=$this->em->getRepository(Rates::class);
        $rate= $Raterepository->findOneBy(['user'=>$user , 'course' => $course]);

        if($rate){
            $rate->setUser($user);
            $rate->setCourse($course);
            $rate->setRate($rating);
            $this->em->persist($rate);
            $this->em->flush();
        }
        else{
            $newrate=new Rates();
            $newrate->setUser($user);
            $newrate->setCourse($course);
            $newrate->setRate($rating);

            $this->em->persist($newrate);
            $this->em->flush();

        }

        $courses = $Raterepository->findBy(['course' => $course]);
        $count=0;
        $somme=0;
        foreach( $courses as $c ){
            $somme += $c->getRate();
            $count++;
        }
        $finalRate = (int)$somme/$count;

        $course->setRating($finalRate);

        $this->em->persist($course);
        $this->em->flush();

        if($index == 1){
            return $this->redirectToRoute('app_main');
        }
        elseif($index==2){
            return $this->redirectToRoute('app_search');
        }
        else{
            return $this->redirectToRoute('app_profile');
        }
    }

    #[Route('/learn/{id}' , name : 'app_learn')]
    public function learn(Course $course){
        $repository =$this->em->getRepository(Video::class);
        //$videos = $course -> getVideos();
        $video = $repository->findOneBy(['courses'=>$course]);

        $user = $this->getUser();
        if($user instanceof User){
            $notifications = $user->getNotifications();
        }
        return $this->render('course/learn.html.twig' , ['course'=>$course , 'video'=>$video , 'notifications'=>$notifications]);   
    }

    #[Route('/search/{page}' , name : 'app_search' , defaults: ['page' => 1])]
    public function search(Request $request , PaginatorInterface $paginator , int $page){
        $value = $request->request->get('search');
        $session = $request->getSession();
        if($value){
            $session->set('searched_value' ,$value);
        }
        else{
            $value=$session->get('searched_value');
        }
        
        $repository = $this->em->getRepository(Course::class);

        $query = $repository->createQueryBuilder('c')
            ->where('c.title LIKE :title')
            ->orWhere('c.description LIKE :description')
            ->orWhere('c.category LIKE :category')
            ->setParameter('title', '%'. $value .'%')
            ->setParameter('description', '%'. $value .'%')
            ->setParameter('category', '%'. $value .'%')
            ->getQuery();

        // Paginate results (n courses per page)
        $pagination = $paginator->paginate(
            $query, 
            $request->query->getInt('page', $page), // Get page number from URL, default is 1
            6 // n of courses per page
        );

        $notification = new notification();
        $repository=$this->em->getRepository(Notification::class);
        $user=$this->getUser();
        $notification=$repository->findBy(['users'=>$user]);

        return $this->render('search.html.twig' , ['coursePagination' =>$pagination , 'search' => $value , 'notifications'=>$notification]); 
    }

    #[Route('/filter/{page}/{value?}', name: 'app_filter' , defaults: [ 'page' => 1 , 'value' => ''])]
    public function filter(Request $request, EntityManagerInterface $em , PaginatorInterface $paginator , int $page , string $value='')
    {

        $language = $request->request->get('language', '');
        $category = $request->request->get('category', '');
        $rating = $request->request->get('rating', '');
        $level = $request->request->get('level', '');

        $repository = $em->getRepository(Course::class);
        $queryBuilder = $repository->createQueryBuilder('c');

        // (c.title LIKE :title OR c.description LIKE :description)
        $searchCondition = $queryBuilder->expr()->orX(
            $queryBuilder->expr()->like('c.title', ':title'),
            $queryBuilder->expr()->like('c.description', ':description')
        );

        // (c.category LIKE :category AND c.language LIKE :language AND c.rating LIKE :rating AND c.level LIKE :level)
        $filterCondition = $queryBuilder->expr()->andX(
            $queryBuilder->expr()->like('c.category', ':category'),
            $queryBuilder->expr()->like('c.language', ':language'),
            $queryBuilder->expr()->gte('c.rating', ':rating'),
            $queryBuilder->expr()->like('c.level', ':level')
        );

        // Appliquer la condition (searchCondition) AND (filterCondition)
        $query=$queryBuilder->where($searchCondition)
                    ->andWhere($filterCondition)
                    ->setParameter('title', '%' . $value . '%')
                    ->setParameter('description', '%' . $value . '%')
                    ->setParameter('category', '%' . $category . '%')
                    ->setParameter('language', '%' . $language . '%')
                    ->setParameter('rating', $rating)
                    ->setParameter('level', '%' . $level . '%')
                    ->getQuery();
        

        $pagination = $paginator->paginate(
            $query, 
            $request->query->getInt('page', $page), // Get page number from URL, default is 1
            6 // n of courses per page
        );
        $repository=$this->em->getRepository(Notification::class);
        $user=$this->getUser();
        $notification=$repository->findBy(['users'=>$user]);


        return $this->render('search.html.twig', ['coursePagination' => $pagination , 'search'=>$value , 'notifications'=>$notification]);
    }
    #[Route('/notification', name: 'app_notification')]
    public function notification(): Response
    {

        return $this->render('notification.html.twig');
    }

    #[Route('/About' , name: 'app_about')]
    public function deleteNotification(): Response{
        $user = $this->getUser();
        if($user instanceof User){
            $notification = $user->getNotifications();
        }

        return $this->render('about.html.twig' , ['notifications' => $notification]);
    }
    #[Route('/emailSupport' , name: 'app_emailSupport')]
    public function emailSupport(Request $request , MailerInterface $mailer): Response{
        //dd(1);
        $subject = $request->request->get('subject');
        $emailContent=$request->request->get('email');

        //dd($subject);
        

        $user= $this->getUser();
        if($user instanceof User){
            $emailUser =$user->getEmail();
            $fullname = $user->getFullname();
        }

        $email = (new TemplatedEmail())
        ->from($emailUser)
        ->to('uthmanjunaid400@gmail.com')
        ->subject($emailContent)
        ->html('<p>Subject :'.$subject.'</p><p><strong> Hello F-course support , this message is sent by'.$fullname.' </strong></p>,<p>' . $emailContent . '</p>');



        $mailer->send($email);

        // send a notification to the admin
        $repository=$this->em->getRepository(User::class);
        $admin = $repository->createQueryBuilder('u')
        ->andWhere('u.roles LIKE :role')
        ->setParameter('role', '%ROLE_ADMIN%')
        ->getQuery()
        ->getOneOrNullResult();

        $notification = new Notification();
        $notification->setContent($fullname."(email :".$emailUser.") sent you an email for ".$subject." . check your email please.");
        $notification->setUsers($admin);
        $notification->setSender($user);

        $this->em->persist($notification);
        $this->em->flush();

        return $this->redirectToRoute('app_about');
    }
    #[Route('/aboutNonUser' , name:"app_aboutNonlogedIn")]
    public function aboutNonUser(){
        return $this->render('aboutNonUser.html.twig');
    }

    #learn
    
}
//todo : make a filter page that gonna be just like 
//the search page , so u can filter the search and do any other functionality without any problem