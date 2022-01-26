<?php

namespace App\Controller\Api;

use App\Entity\InformationProduct;
use App\Entity\ResearchGroup;
use App\Form\InformationProductType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class InformationProductController extends AbstractFOSRestController
{

    /**
     * @param InformationProduct $informationProduct The id of the information product.
     *
     * @Route (
     *     "/api/information_product/{id}",
     *     name="pelagos_api_get_information_product",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     * )
     *
     * @return Response
     */
    public function getInformationProduct(InformationProduct $informationProduct, SerializerInterface $serializer): Response
    {
        return new Response($serializer->serialize($informationProduct, 'json'));
    }

    /**
     * Creates a new Information Product
     *
     * @param Request $request
     *
     * @return Response
     *
     * @Route (
     *     "/api/information_product",
     *     name="pelagos_api_create_information_product",
     *     methods={"POST"},
     *     defaults={"_format"="json"},
     * )
     */
    public function createInformationProduct(Request $request): Response
    {
        $response = Response::HTTP_BAD_REQUEST;
        $id = null;
        $prefilledRequestDataBag = $this->jsonToRequestDataBag($request->getContent());
        $entityManager = $this->getDoctrine()->getManager();
        $informationProduct = new InformationProduct();
        $form = $this->createForm(InformationProductType::class, $informationProduct);
        $request->request->set($form->getName(), $prefilledRequestDataBag);
        $researchGroupsIds = $request->get('selectedResearchGroups');
        $researchGroups = $entityManager->getRepository(ResearchGroup::class)->findBy(['id' => $researchGroupsIds]);
        foreach ($researchGroups as $researchGroup) {
            $informationProduct->addResearchGroup($researchGroup);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($informationProduct);
            $entityManager->flush();
            $id = $informationProduct->getId();
            $response = Response::HTTP_CREATED;
        }

        return new JsonResponse(['id' => $id], $response);
    }

    /**
     * Updates the Information Product
     *
     * @param Request $request
     * @param InformationProduct $informationProduct
     *
     * @return Response
     *
     * * @Route (
     *     "/api/information_product/{id}",
     *     name="pelagos_api_update_information_product",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     * )
     */
    public function updateInformationProduct(Request $request, InformationProduct $informationProduct): Response
    {
        $form = $this->createForm(InformationProductType::class, $informationProduct);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * Delete Information Product
     *
     * @param Request $request
     * @param InformationProduct $informationProduct
     *
     * @return Response
     *
     * @Route (
     *     "/api/information_product/{id}",
     *     name="pelagos_api_deletet_information_product",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     * )
     */
    public function deleteInformationProduct(Request $request, InformationProduct $informationProduct): Response
    {
        if ($this->isCsrfTokenValid('delete'.$informationProduct->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($informationProduct);
            $entityManager->flush();

            return new JsonResponse(Response::HTTP_OK);
        }
    }

    /**
     * Will output an array which can be inserted into the @param string $json
     * @return array
     * @throws \Exception
     * @throws Exception*@see Request::request::set
     * Such request can be then passed to proper form @see FormInterface::handleRequest()
     * With this - data sent via axios post can be processed like it normally should like via standard POST call
     *
     */
    private function jsonToRequestDataBag(string $json): array
    {
        $dataArray = json_decode($json, true);

        if( JSON_ERROR_NONE !== json_last_error() ){
            $message = "Provided json is not valid";
            $this->logger->critical($message, [
                'jsonLastErrorMessage' => json_last_error_msg(),
            ]);

            throw new \Exception($message, Response::HTTP_BAD_REQUEST);
        }

        return $dataArray;
    }
}
