<?php 

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Email as EmailConstraint;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Model\UserInterface;

class WelcomeController extends Controller
{
    /**
     * @Route("/google-login", name="googleloginform")
     * @Method({"GET", "POST"})
     */
    public function loginAction(Request $request) {

        $client= new \Google_Client();
        $client->setApplicationName('symfonyApp');
        $client->setClientId('588530792979-cd07nit4ki9l2ksj4kms3cs0fqeglii0.apps.googleusercontent.com');
        $client->setClientSecret('Y5Bdosz-rgL92J0CuF_VNW8L');
        $client->setRedirectUri('http://localhost:8001/security');

        $client->addScope('https://www.googleapis.com/auth/plus.login');

        $url= $client->createAuthUrl();

        echo "<button class= btn btn-danger ><a href='$url'>Log in with Google!</a></button>";
        die;
    }

    /**
     * @Route("/delete", name="delete")
     */
    public function deleteAction(Request $request)
    {
        $session = $request->getSession();
        $session->getFlashBag()->clear();
        $session->invalidate();
        return $this->redirect("/");        
        //die;
    }

    /**
     * @Route("/security", name="security")
     */
    public function protectCurrentPage(Request $request) {

        $session = new Session();     
        $session->set('isLoggedIn', "true");

        if($session === null) {
            return $this->redirect("/");
        } else {
            $session->getFlashBag()->add('notice', 'Successful Login! Welcome!'); 
            return $this->redirect("/hello");
        }

    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request) {
        
        $session = $request->getSession();

        $user = $session->get('user');
        
        return $this->render('templates/index.html.twig', array( "name" => "home", "title" => "Home" ));
    }

    /**
     * @Route("/", name="userspage")
     */
    public function usersAction() {

        $em = $this->getDoctrine()->getManager();
        $conn = $em->getConnection();

        $stmt = $conn->prepare("select * from users");
        $stmt->execute();
        $results = $stmt->fetchAll();

        $data = array("title"=>"Users", "users"=>$results);
        return $this->render('templates/users.html.twig', array( "name" => "users", "title" => "Users" ), $data);
    }

    /**
     * @Route("/hello", name="hellopage")
     */
    public function helloAction(Request $request) {


        $session = $request->getSession();
        $isLoggedIn = $session->get('isLoggedIn');
        

        if($isLoggedIn === null) {
            return $this->redirect("/");
        } else {
            return $this->render('templates/hello.html.twig', array( "name" => "hello", "title" => "Hello" ));            
        }
   
    }

    /**
     * @Route("/product", name="productpage")
     */
    public function productAction(Request $request) {

        $session = $request->getSession();
        $isLoggedIn = $session->get('isLoggedIn');

        
        if($isLoggedIn === null) {
            return $this->redirect("/");
        } else {
            $response = $this->forward('App\Controller\ProductController::fancy');
            return $response;    
        }

    }

    /**
     * @Route("/registerForm", name="registerForm")
     */
    public function registerForm(Request $request) {

        $session = $request->getSession();
        $isLoggedIn = $session->get('isLoggedIn');
        
        if($isLoggedIn === null) {
            return $this->redirect("/");
        } else {
        
            $form = $this->createFormBuilder(null)
            ->setAction($this->generateUrl("registerForm"))
            ->add("name", TextType::class, array("required"=>true, "constraints"=>[
                new NotBlank(array("message"=>"Can not be blank."))
                ]))
            ->add("email", TextType::class, array("required"=>true, "constraints"=> [
                new EmailConstraint(array("message"=>"This is not the correct way of typing an email.")),
                new NotBlank(array("message"=>"Can not be blank."))
            ]))
            ->add("myfile", FileType::class, array("constraints"=>[
                new File(array(
                    'maxSize' => '2M',
                    'mimeTypes' => [
                        'application/pdf',
                        'application/x-pdf'],
                    'mimeTypesMessage' => 'Please upload a valid PDF'
                ))
            ]))
            ->add("save", SubmitType::class)
            ->getForm();

            $form->handleRequest($request);

            if ($request->isMethod("POST")) {

                if ($form->isValid()) {

                    $file = $form->get("myfile")->getData();
                    $fileName = md5(uniqid()).".".$file->guessExtension();
                    $file->move("/Users/jweber0169/Documents/asl/symfony", $fileName);

                    return $this->render("templates/regdone.html.twig", array("title"=>"Register"));

                }

            }

            return $this->render('templates/registerForm.html.twig', array( "title" => "Register", "form" => $form->createView() ));
        }

    }

}



        //$session = $request->getSession();
        //$this->protectCurrentPage($request);     
        //$session = $request->getSession();
        //$isLoggedIn = $session->get('isLoggedIn');
        // $tester = $this->get('session')->get('test');