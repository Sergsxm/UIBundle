<?php

/**
 * File ajax upload controller
 * This file is part of SergSXM UI package
 *
 * @package    SergSXM UI
 * @author     SergSXM <sergsxm@embedded.by>
 * @copyright  2016 SergSXM
 */

namespace Sergsxm\UIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sergsxm\UIBundle\FormInputTypes\File as FileInputType;

class FileController extends Controller
{

/**
 * Ajax upload action
 * 
 * @return JsonResponse
 */    
    public function uploadAction()
    {
        $request = $this->get('request_stack')->getMasterRequest();
        
        $formId = $request->get('form_id');
        if ($formId == null) {
            return new JsonResponse(array('error' => 'Unknown formId'));
        }
        $formBag = new \Sergsxm\UIBundle\Classes\FormBag($request->getSession());
        $formBag->setFormId($formId);
        $fieldParameters = $formBag->get($request->get('input_name'));
        if (!is_array($fieldParameters)) {
            return new JsonResponse(array('error' => 'Unknown inputName'));
        }
        $file = $this->getRequest()->files->get($request->get('input_name'));
        if (empty($file)) {
            return new JsonResponse(array('error' => 'Upload error'));
        }
        try {
            if (($fieldParameters['mimeTypes'] != null) && (!in_array($file->getMimeType(), $fieldParameters['mimeTypes']))) {
                return new JsonResponse(array('error' => 'Invalid file type'));
            }
            if (($fieldParameters['maxSize'] != null) && ($file->getSize() > $fieldParameters['maxSize'])) {
                return new JsonResponse(array('error' => 'File size is larger than allowed'));
            }
            if ($fieldParameters['storeType'] == FileInputType::ST_FILE) {
                $value = new \Sergsxm\UIBundle\Classes\File();
            } elseif ($fieldParameters['storeType'] == FileInputType::ST_DOCTRINE) {
                $value = new $fieldParameters['storeDoctrineClass'];
            }
            $value->setFileName($file->getClientOriginalName());
            $value->setMimeType($file->getMimeType());
            $value->setUploadDate(new \DateTime('now'));
            do {
                $randomBytes = pack('L', time()).random_bytes(20);
                $newFileName = rtrim(strtr(base64_encode($randomBytes), '/+', '-_'), '=');
            } while (file_exists($fieldParameters['storeFolder'].DIRECTORY_SEPARATOR.$newFileName));
            $file->move($fieldParameters['storeFolder'], $newFileName);
            $value->setContentFile($fieldParameters['storeFolder'].DIRECTORY_SEPARATOR.$newFileName);
            if ($fieldParameters['storeType'] == FileInputType::ST_FILE) {
                $value->storeInfo();
            } elseif ($fieldParameters['storeType'] == FileInputType::ST_DOCTRINE) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($value);
                $em->flush();
            }
        } catch (\Exception $e) {
            return new JsonResponse(array('error' => 'Exception: '.$e->getMessage()));
        }
        return new JsonResponse(array(
            'fileName' => $value->getFileName(),
            'contentFile' => $value->getContentFile(),
            'size' => $value->getSize(),
            'uploadDate' => $value->getUploadDate(),
            'mimeType' => $value->getMimeType(),
            'id' => $value->getId(),
        ));
    }
}
