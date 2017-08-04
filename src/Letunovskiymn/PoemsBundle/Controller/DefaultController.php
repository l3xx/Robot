<?php

namespace Letunovskiymn\PoemsBundle\Controller;

use FOS\RestBundle\View\View;
use Letunovskiymn\PoemsBundle\Entity\Poem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="poems_bundle_default_index")
     */
    public function indexAction()
    {
        return $this->render('LetunovskiymnPoemsBundle:Default:index.html.twig');
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      resourceDescription="Operations on phrase",
     *      description="Add phrase to database.",
     *      statusCodes={
     *         200 = "Phrase added.",
     *         400 = "Duplicate phrase or database error.",
     *         406 = "NULL VALUES ARE NOT ALLOWED.",
     *      },
     * )
     * @Rest\Post("/phrase")
     * @param Request $request
     * @return View
     */
    public function addPhraseAction(Request $request)
    {
        $data = new Poem();
        $text = $request->get('text');
        $position = $request->get('position');
        if(empty($text) || !isset($position) || $this->checkByPosition($text,$position)==FALSE)
        {
            return new View("NULL VALUES ARE NOT ALLOWED", Response::HTTP_NOT_ACCEPTABLE);
        }
        $data->setText($text);
        $data->setPosition($position);
        $em = $this->getDoctrine()->getManager();
        $em->persist($data);
        try{
            $em->flush();
        }
        catch (\Exception $e){
            return new View("Duplicate phrase", Response::HTTP_BAD_REQUEST);
        }

        return new View("Phrase Added Successfully", Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      resource=true,
     *      resourceDescription="Operations on phrase",
     *      description="Delete phrase from database.",
     *      statusCodes={
     *         200 = "Phrase deleted.",
     *         404 = "Phrase not found.",
     *      },
     * )
     * @Rest\Delete("/phrase-delete/{id}")
     * @param $id
     * @return View
     */
    public function deletePhraseAction($id)
    {
        $sn = $this->getDoctrine()->getManager();
        $phrase = $this->getDoctrine()->getRepository('LetunovskiymnPoemsBundle:Poem')->find($id);
        if (empty($phrase)) {
            return new View("user not found", Response::HTTP_NOT_FOUND);
        }
        else {
            $sn->remove($phrase);
            $sn->flush();
        }
        return new View("deleted successfully", Response::HTTP_OK);
    }


    /**
     * @ApiDoc(
     *      resource=true,
     *      resourceDescription="Operations on phrase",
     *      description="List phrase from database.",
     *      statusCodes={
     *         200 = "list phrases.",
     *         404 = "There are no phrases exist.",
     *      },
     * )
     * @Rest\Get("/phrases")
     */
    public function getPhrasesAction()
    {
        $restResult = $this->getDoctrine()->getRepository('LetunovskiymnPoemsBundle:Poem')->findAll();
        if ($restResult === null) {
            return new View("there are no phrases exist", Response::HTTP_NOT_FOUND);
        }
        return $restResult;
    }


    /**
     * @ApiDoc(
     *      resource=true,
     *      resourceDescription="Operations on phrase",
     *      description="List phrase from database.",
     *      statusCodes={
     *         200 = "Make poem"
     *      },
     * )
     * @Rest\Get("/make-poem")
     */
    public function getMakePoemAction()
    {
        $result = $this->getDoctrine()->getRepository('LetunovskiymnPoemsBundle:Poem')->getRandomPoems();
        $restResult=['poem'=>''];
        foreach ($result as $item){
            $restResult['poem'] .=$item->getText()." ";
        }
        $restResult['poem']=trim($restResult['poem']);
        return $restResult;
    }


    private function checkByPosition($text, $position){
        $result=false;
        $text=mb_strtolower($text);
        $tail=mb_substr($text, mb_strlen($text)-2, 2);
        switch ($position){
            case 0:
                if ($tail=='ая' || $tail=='ые' || $tail=='ие'){
                    $result=true;
                }
                break;
            case 1:
                if ($tail=='зы' || $tail=='за' ){
                    $result=true;
                }
                break;
            case 2:
                $result=true;
                break;
            case 3:
                if ($tail=='ом' || $tail=='ем' || $tail=='ём'){
                    $result=true;
                }
                break;
            case 4:
                if ($tail=='сь'){
                    $result=true;
                }
                break;
            case 5:
                $result=true;
                break;
            case 6:
                if ($tail=='но' || $tail=='ко' || $tail=='то'){
                    $result=true;
                }
                break;
            case 7:
                if ($tail=='ом' || $tail=='ем' || $tail=='ём'){
                    $result=true;
                }
        }

        return $result;
    }

}
