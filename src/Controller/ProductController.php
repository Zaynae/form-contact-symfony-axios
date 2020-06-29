<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Product;
use App\Form\ContactType;
use App\Service\MailerHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{

    private $products;

    public function __construct()
    {
        $product1 = new Product();
        $product1->setName("iPhone 11 Pro 512Go Noir Neuf")
            ->setImage("img/products/apple-iphone-11-pro-max-gris-sideral-512-go.jpg")
            ->setPrice(1400)
            ->setSlug("apple-iphone-11-pro-max-gris-sideral-512-go")
            ->setDescription("");
        $product2 = new Product();
        $product2->setName("iPhone 11 64Go Rouge Neuf")
            ->setImage("img/products/apple-iphone-11-rouge-64-go.jpg")
            ->setPrice(860)
            ->setSlug("apple-iphone-11-rouge-64-go")
            ->setDescription("");
        $product3 = new Product();
        $product3->setName("iPhone 11 64Go Blanc Neuf")
            ->setImage("img/products/apple-iphone-11-blanc-64-go.jpg")
            ->setPrice(920)
            ->setSlug("apple-iphone-11-blanc-64-go")
            ->setDescription("");

        $this->products = [$product1,$product2,$product3];


    }


    /**
     * @Route("/", name="product-index")
     */
    public function index()
    {

        return $this->render('product/index.html.twig',[
            "products" => $this->products,
        ]);
    }


    /**
     * @Route("/sendmessage", name="product-sendmessage")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param MailerHandler $mailerHandler
     * @param EntityManagerInterface $manager
     * @return JsonResponse
     */
    public function sendmessage(Request $request,
                                ValidatorInterface $validator,
                                MailerHandler $mailerHandler,
                                EntityManagerInterface $manager){

        $data = $request->request->get('contact');

        $token = $data['_token'];
        $contact = new Contact();
        $contact->setName($data['name'])
                ->setEmail($data['email'])
                ->setProduct($data['product'])
                ->setMessage($data['message']);

        $failed = true;
        $success = false;
        $errors = $validator->validate($contact);
        if($this->isCsrfTokenValid('contact-token',$token)){
           if(count($errors)==0){
               $manager->persist($contact);
               $manager->flush();
               $success = true;
               $mailerHandler->sendMailContact($contact);
           }
            $failed = false;
        }

        return $this->json([
            'errors' => $errors,
            'failed' => $failed,
            'success' => $success,
        ],200);

    }


    /**
     * @Route("/{slug}", name="product-view")
     */
    public function view($slug){

        $product = array_filter($this->products,function ($entity) use ($slug){
            return $entity->getSlug() === $slug;
        });
        $contact = new Contact();
        $form = $this->createForm(ContactType::class,$contact, [
            'action' => '',
            'method' => 'Post',
        ]);



        return $this->render("product/view.html.twig",[
            'product' => reset($product),
            'form' => $form->createView(),
        ]);
    }


}
