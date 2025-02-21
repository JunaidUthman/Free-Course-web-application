<?php

namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Course;
use App\Entity\Notification;
use App\Entity\Video;
use App\Entity\Like;
use App\Entity\Rates;
use App\Entity\Comment;
use App\Entity\CheckedAccounts;
use App\Entity\DeletedAccounts;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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



final class AdminController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em){
        $this->em=$em;
    }

    #[Route('/admin', name: 'admin')]
    public function admin(){
        return $this->redirectToRoute('app_login');
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(){
        $user = $this->getUser();

        $checkeduserReposetory = $this->em->getRepository(CheckedAccounts::class);
        $lastAccounts = $checkeduserReposetory->findBy(['admin'=>$user], ['id' => 'DESC'], 3);

        $userReposetory = $this->em->getRepository(User::class);
        $n_accounts = $userReposetory->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $n_frozen = $userReposetory->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.frozen = 1')
            ->getQuery()
            ->getSingleScalarResult();  
            
        $n_active = $userReposetory->createQueryBuilder('u')
            ->select('count(u.id)')
            ->where('u.frozen =0 ')
            ->getQuery()
            ->getSingleScalarResult();
         
        $videoReposetory = $this->em->getRepository(Video::class);
        $n_videos = $videoReposetory->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $couseReposetory = $this->em->getRepository(Course::class);
        $n_courses=$couseReposetory->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $trending_courses=$couseReposetory->createQueryBuilder('c')
            ->select('c.category, COUNT(c.id) as course_count')
            ->groupBy('c.category')
            ->orderBy('course_count', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        //dd($trending_courses);

        
        $repository = $this->em->getRepository(Notification::class);
        $notifications = ($repository->findBy(['users'=>$user],['id' => 'DESC'], 3));
        $Allnotifications = ($repository->findBy(['users' => $user]));


        $repository = $this->em->getRepository(User::class);
        $users = $repository->findBy([], null, 5);

        $deleteRepository= $this->em->getRepository(DeletedAccounts::class);
        $n_deleted = $deleteRepository->createQueryBuilder('u')
            ->select('count(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        //dd($n_deleted); 

        return $this->render('admin/dashboard.html.twig' , ['notifications' => $notifications,'n_deleted'=>$n_deleted,'n_frozen'=>$n_frozen ,'n_active'=>$n_active,'trending_courses'=>$trending_courses , 'users'=>$users ,'Allnotifications'=>$Allnotifications , 'n_accounts' => $n_accounts , 'n_videos' => $n_videos , 'n_courses' => $n_courses , 'lastAccounts' => $lastAccounts]);

    }

    #[Route('/admin/profile' , name: 'admin_profile')]
    public function profile(){
        $user = $this->getUser();
        $repository = $this->em->getRepository(Notification::class);
        $notifications = $repository->findBy(['users'=>$user]);
        return $this->render('admin/profile.html.twig' , ['Allnotifications'=>$notifications]);
    }
    #[Route('/admin/users/{page}' , name: 'admin_users' , defaults: ['page' => 1])]
    public function users(Request $request , PaginatorInterface $paginator , int $page) : Response{
        $repository = $this->em->getRepository(User::class);

        $query = $repository->createQueryBuilder('u')
        ->orderBy('u.id') 
        ->getQuery();

        // Paginate results (n courses per page)
        $pagination = $paginator->paginate(
            $query, 
            $request->query->getInt('page', $page), // Get page number from URL, default is 1
            7 // n of courses per page
        );

        $user = $this->getUser();
        $repository = $this->em->getRepository(Notification::class);
        $notifications = $repository->findBy(['users'=>$user]);
        return $this->render('admin/users.html.twig' , ['userPaginator' => $pagination , 'Allnotifications' => $notifications]);
    }
    #[Route('/admin/usersInfo/{id}' , name: 'seeMore')]
    public function userInfo(User $user): Response{
        $admin= $this->getUser();
        $check = new CheckedAccounts();
        
        $check ->setAdmin($admin);
        $check ->setCheckedUser($user);
        $dateString = (new \DateTime())->format('Y-m-d H:i:s');
        $check ->setDate($dateString);

        $this->em->persist($check);
        $this->em->flush();

        return $this->render('admin/userInfo.html.twig' , ['user' => $user]);
    }

    #[Route('/admin/checkCourse/{id}' , name : 'checkCourse')]
    public function checkCourse(Course $course) : Response{
        $repository = $this->em->getRepository(Course::class);
        $video = $course->getVideos()->first();

        
        return $this->render('admin/checkCourse.html.twig' , ['course' => $course , 'video' => $video]);
    }

    #[Route('/admin/sendEmail/{id}' , name: 'sendEmail')]
    public function sendEmail(Request $request , MailerInterface $mailer , User $user){
        $date = new \DateTime();
        $this_user=$this->getUser();
        //dd($this_user);

        $emailContent = $request->request->get('email');
        //dd($emailContent);

        $email = (new TemplatedEmail())
        ->from('uthmanjunaid400@gmail.com')
        ->to($user->getEmail())
        ->subject($emailContent)
        ->html('<p>Hello ' . $user->getFullname() . ',</p><p>' . $emailContent . '</p>');

        $mailer->send($email);

        $notification = new Notification();
        if($notification instanceof Notification){
            $notification->setUsers($user);
            $notification->setSender($this_user);
            $notification->setContent("The Support team of FREE COURSE sent you an email , check your email Please.");
            $this->em->persist($notification);
            $this->em->flush();
        }
       

        return $this->redirectToRoute('seeMore' , ['id' => $user->getId()]);

    }

    #[Route('/admin/freeze/{id}' , name: 'freeze')]
    public function freeze(User $user){
        $user->setFrozen(true);
        $this->em->persist($user);
        $this->em->flush();
        return $this->redirectToRoute('seeMore' , ['id' => $user->getId()]);
    }

    #[Route('/admin/unfreeze/{id}' , name: 'unfreeze')]
    public function unfreeze(User $user){
        $user->setFrozen(false);
        $this->em->persist($user);
        $this->em->flush();
        return $this->redirectToRoute('seeMore' , ['id' => $user->getId()]);
    }

    #[Route('/admin/delete/{id}' , name: 'delete_account')]
    public function delete(User $user){

        $delete = new DeletedAccounts();
        $delete->setDateDelete((new \DateTime())->format('Y-m-d'));

        $this->em->persist($delete);
        $this->em->flush();

        $this->em->remove($user);
        $this->em->flush();

        return $this->redirectToRoute('admin_users');
    }
    #[Route('/admin/search', name: 'admin_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $userRepository = $this->em->getRepository(User::class);
        $query = $request->query->get('query', '');
        $users = $userRepository->createQueryBuilder('u')
            ->where('u.fullname LIKE :query OR u.email LIKE :query')
            ->andWhere('u.roles NOT LIKE :role')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('role', '["ROLE_ADMIN"]')
            ->getQuery()
            ->getResult();

        $responseArray = [];
        foreach ($users as $user) {
            $responseArray[] = [
                'id' => $user->getId(),
                'fullname' => $user->getFullname(),
                'email' => $user->getEmail(),
                'imagePath' => $user->getImagePath()
            ];
        }

        return new JsonResponse($responseArray);
    }

    #[Route('/admin/courses/{page}', name: 'admin_Courses' , defaults: ['page' => 1])]
    public function displaycourses(PaginatorInterface $paginator , Request $request , int $page){
        $repository = $this->em->getRepository(Course::class);

        $query = $repository->createQueryBuilder('c')
        ->orderBy('c.id', 'DESC') // Sorting by latest courses
        ->getQuery();

        // Paginate results (n courses per page)
        $pagination = $paginator->paginate(
            $query, 
            $request->query->getInt('page', $page), // Get page number from URL, default is 1
            6 // n of courses per page
        );
        //$courses = $repository->findAll();

        $user= $this->getUser();

        $repository = $this->em->getRepository(Notification::class);
        $Allnotifications = $repository->findBy(['users'=>$user]);

        return $this->render('admin/courses.html.twig' , ['pagination' => $pagination , 'Allnotifications' => $Allnotifications]);
    }

    #[Route('/admin/courseSearch', name: 'admin_courseSearch', methods: ['GET'])]
    public function searchCourse(Request $request): JsonResponse
    {
        $CourseRepository = $this->em->getRepository(Course::class);
        $query = $request->query->get('query', '');
        $courses = $CourseRepository->createQueryBuilder('u')
            ->where('u.title LIKE :query OR u.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        $responseArray = [];
        foreach ($courses as $course) {
            $responseArray[] = [
                'id' => $course->getId(),
                'title' => $course->getTitle(),
                'description' => $course->getDescription(),
                'courseImage' => $course->getCourseImage(),
                'rating' =>$course ->getRating(),
                'image' => $course->getCourseImage()
            ];
        }

        return new JsonResponse($responseArray);
    }

    #[Route('/checkVideo/{id}' , name: 'checkVideo')]
    public function checkVideo(Course $course){
        $repository =$this->em->getRepository(Video::class);
        //$videos = $course -> getVideos();
        $video = $repository->findOneBy(['courses'=>$course]);
        return $this->render('admin/checkVideo.html.twig' , ['course'=>$course , 'video'=>$video]);   
    }


}