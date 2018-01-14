<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use AppBundle\Form\CodeBarreType;
use AppBundle\Form\EvaluationType;
use AppBundle\Entity\Product;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template("index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(CodeBarreType::class);

        /**** Latest viewed ****/
        $em = $this->getDoctrine()->getManager();
        $pdtLastV = $em->getRepository("AppBundle:Product")->findBy(array(),array('lastViewDate' => 'desc'),8);

        $dataPdt =array();
        foreach ($pdtLastV as $lastV) {
            $url = 'https://fr.openfoodfacts.org/api/v0/produit/'.$lastV->getBarCode().'.json';
            $data = json_decode(file_get_contents($url), true);
            $dataPdt[] = $data['product'];
        }

        /**** Best marked ****/
        $bestMarks = $em->getRepository("AppBundle:Product")->findBestProducts();

        $best=array();
        foreach ($bestMarks as $bestM ) { 
            $url = 'https://fr.openfoodfacts.org/api/v0/produit/'.$bestM["barCode"].'.json';
            $data = json_decode(file_get_contents($url), true);
            $best[] = $data['product'];
        }

        return [
            'form' => $form->createView(),
            'dataPdt' => $dataPdt,
            'bests' => $best
        ];
    }

    /**
     * @Route("/search", name="search")
     * @Template("search.html.twig")
     */
    public function searchAction(Request $request)
    {
        $form = $this->createForm(CodeBarreType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $code_barre = $data['code_barre'];

            //Verify is product exist in API
            $url = 'https://fr.openfoodfacts.org/api/v0/produit/'.$code_barre.'.json';
            $data = json_decode(file_get_contents($url), true);

            if ($data["status"] == 1){

                //chercher si le produit existe
                $em = $this->getDoctrine()->getManager();
                $repo = $em->getRepository("AppBundle:Product");
                $pdt = $repo->findOneBy(['barCode' => $code_barre]);

                if($pdt == null){
                    //le créer en base si existe pas
                    $newPdt = new Product;
                    $newPdt -> setBarCode($code_barre)
                            -> setNbConsultation(1)
                            -> setLastViewDate(Date('d m Y H:i:s'));
                    $em->persist($newPdt); 
                    $em->flush();

                }else {
                    //si existe incrémenter nb_consultation, maj date_lastView date now
                    $nbView = $pdt -> getNbConsultation()+1;
                              $pdt -> setNbConsultation($nbView);
                              $pdt -> setLastViewDate(Date('d m Y H:i:s'));

                    $em->flush();
                }

                return $this->redirectToRoute('product', array('cb' => $code_barre));
            }else {
                return [ 'code_barre' => $code_barre ];
            }
        } else {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/product/cb={cb}", name="product")
     * @Template("product.html.twig")
     */
    public function productAction(Request $request, $cb)
    {
        $form = $this->createForm(EvaluationType::class);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();        

        //Get product informations 
        $url = 'https://fr.openfoodfacts.org/api/v0/produit/'.$cb.'.json';
        $data = json_decode(file_get_contents($url), true);
        $dataPdt = $data['product'];

        //Find product with barCode passed by url in database
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository("AppBundle:Product");
        $pdt = $repo->findOneBy(['barCode' => $cb]);

        //Get current user
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $username = $user->getUsername();

        //Get User id 
        $repo = $em->getRepository("AppBundle:User");
        $userId = $repo->findOneBy(['username' => $username])->getId();
        
        //Get product id 
        $pdtId = $pdt->getId();

        //Voir si l'utilisateur a deja poster un com 
        $repo = $em->getRepository("AppBundle:Evaluation");
        $postEval = $repo->findOneBy(['user' => $userId, 'product' => $pdtId ]);
        

        // Get mark average 
        $avgMarks = $em->getRepository("AppBundle:Product")->findMark($pdtId);
        $mark= $avgMarks[0];

        
        //Notation form 
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $mark = $data['mark'];
            $comment = $data['comment'];

            $newEval = new Evaluation();
            $newEval -> setUser($userId)
                     -> setProduct($pdtId)
                     -> setMark($mark)
                     -> setcomment($comment);

            $em->persist($newEval); 
            $em->flush();
        }

        return [
            'form' => $form->createView(),
            'username' => $username,
            'pdt' => $pdt, 
            'ingredient' => $dataPdt['ingredients_text'],
            'code_barre' => $cb,
            'dataPdt' => $dataPdt,
            'mark' => $mark,
            'hasPost' => $postEval
        ];  
    }
}
