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
use Symfony\Component\HttpFoundation\Response;
use Sergsxm\UIBundle\FormInputTypes\File as FileInputType;
use Sergsxm\UIBundle\FormInputTypes\Image as ImageInputType;

class FileController extends Controller
{
    const imageWidth = 180;
    const imageHeight = 140;
    const quality = 75;
    const backgroundRed = 238;
    const backgroundGreen = 238;
    const backgroundBlue = 238;
    
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
            return new JsonResponse(array('error' => 'Unknown form ID'), 406);
        }
        $formBag = new \Sergsxm\UIBundle\Form\FormBag($request->getSession());
        $formBag->setFormId($formId);
        $inputName = $request->get('input_name');
        $fieldParameters = $formBag->get($inputName);
        if (!is_array($fieldParameters) || (($fieldParameters['type'] != 'file') && ($fieldParameters['type'] != 'image'))) {
            return new JsonResponse(array('error' => 'Unknown input name'), 406);
        }
        $thumbnail = '';
        $file = $this->getRequest()->files->get($request->get('input_name'));
        if (empty($file)) {
            return new JsonResponse(array('error' => 'Upload error'), 406);
        }
        if ($file->isValid() == false) {
            return new JsonResponse(array('error' => $file->getError()), 406);
        }
        try {
            if (($fieldParameters['maxSize'] != null) && ($file->getSize() > $fieldParameters['maxSize'])) {
                return new JsonResponse(array('error' => $fieldParameters['maxSizeError']), 406);
            }
            if ($fieldParameters['type'] == 'file') {
                if ($fieldParameters['storeType'] == FileInputType::ST_FILE) {
                    $value = new \Sergsxm\UIBundle\Form\File();
                } elseif ($fieldParameters['storeType'] == FileInputType::ST_DOCTRINE) {
                    $value = new $fieldParameters['storeDoctrineClass'];
                }
            } elseif ($fieldParameters['type'] == 'image') {
                if ($fieldParameters['storeType'] == ImageInputType::ST_FILE) {
                    $value = new \Sergsxm\UIBundle\Form\Image();
                } elseif ($fieldParameters['storeType'] == ImageInputType::ST_DOCTRINE) {
                    $value = new $fieldParameters['storeDoctrineClass'];
                }
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
            if ($fieldParameters['type'] == 'file') {
                if (($fieldParameters['mimeTypes'] != null) && (!in_array($file->getMimeType(), $fieldParameters['mimeTypes']))) {
                    @unlink($value->getContentFile());
                    return new JsonResponse(array('error' => $fieldParameters['mimeTypesError']), 406);
                }
            } elseif ($fieldParameters['type'] == 'image') {
                $imageInfo = @getimagesize($value->getContentFile());
                if (!isset($imageInfo[0]) || !isset($imageInfo[1])) {
                    @unlink($value->getContentFile());
                    return new JsonResponse(array('error' => $fieldParameters['notImageError']), 406);
                }
                if (!in_array($imageInfo[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
                    @unlink($value->getContentFile());
                    return new JsonResponse(array('error' => $fieldParameters['notImageError']), 406);
                }
                if (($fieldParameters['minWidth'] !== null) && ($imageInfo[0] < $fieldParameters['minWidth'])) {
                    @unlink($value->getContentFile());
                    return new JsonResponse(array('error' => $fieldParameters['imageSizeError']), 406);
                }
                if (($fieldParameters['maxWidth'] !== null) && ($imageInfo[0] > $fieldParameters['maxWidth'])) {
                    @unlink($value->getContentFile());
                    return new JsonResponse(array('error' => $fieldParameters['imageSizeError']), 406);
                }
                if (($fieldParameters['minHeight'] !== null) && ($imageInfo[1] < $fieldParameters['minHeight'])) {
                    @unlink($value->getContentFile());
                    return new JsonResponse(array('error' => $fieldParameters['imageSizeError']), 406);
                }
                if (($fieldParameters['maxHeight'] !== null) && ($imageInfo[1] > $fieldParameters['maxHeight'])) {
                    @unlink($value->getContentFile());
                    return new JsonResponse(array('error' => $fieldParameters['imageSizeError']), 406);
                }
            }
            if ($fieldParameters['type'] == 'file') {
                if ($fieldParameters['storeType'] == FileInputType::ST_FILE) {
                    $value->storeInfo();
                } elseif ($fieldParameters['storeType'] == FileInputType::ST_DOCTRINE) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($value);
                    $em->flush($value);
                }
            } elseif ($fieldParameters['type'] == 'image') {
                if ($fieldParameters['storeType'] == ImageInputType::ST_FILE) {
                    $value->storeInfo();
                } elseif ($fieldParameters['storeType'] == ImageInputType::ST_DOCTRINE) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($value);
                    $em->flush($value);
                }
                $thumbnail = $this->container->get('router')->generate('sergsxm_ui_file_thumbnail', array(
                    'form_id' => $formId, 
                    'input_name' => $inputName,
                    'id' => $value->getId()
                ));
            }
        } catch (\Exception $e) {
            return new JsonResponse(array('error' => 'Exception: '.$e->getMessage()), 406);
        }
        return new JsonResponse(array(
            'fileName' => $value->getFileName(),
            'contentFile' => $value->getContentFile(),
            'size' => $value->getSize(),
            'uploadDate' => $value->getUploadDate(),
            'mimeType' => $value->getMimeType(),
            'id' => $value->getId(),
            'thumbnail' => $thumbnail,
        ));
    }

/**
 * Get thumbnail of image
 * 
 * @return Response
 */    
    public function thumbnailAction()
    {
        $request = $this->get('request_stack')->getMasterRequest();
        
        $formId = $request->get('form_id');
        if ($formId == null) {
            return new Response('Unknown form ID', 406);
        }
        $formBag = new \Sergsxm\UIBundle\Form\FormBag($request->getSession());
        $formBag->setFormId($formId);
        $fieldParameters = $formBag->get($request->get('input_name'));
        if (!is_array($fieldParameters) || ($fieldParameters['type'] != 'image')) {
            return new Response('Unknown input name', 406);
        }
        $id = $request->get('id');
        if ($fieldParameters['storeType'] == ImageInputType::ST_FILE) {
            $image = \Sergsxm\UIBundle\Form\Image::restore($id);
        } elseif ($fieldParameters['storeType'] == ImageInputType::ST_DOCTRINE) {
            $image = $this->container->get('doctrine')->getRepository($fieldParameters['storeDoctrineClass'])->find($id);
        }
        if (empty($image)) {
            return new Response('Unknown ID', 406);
        }
        $params = getimagesize($image->getContentFile());
        $source = '';
        switch ($params[2]) {
            case IMAGETYPE_GIF: $source = @imagecreatefromgif($image->getContentFile()); break;
            case IMAGETYPE_JPEG: $source = @imagecreatefromjpeg($image->getContentFile()); break;
            case IMAGETYPE_PNG: $source = @imagecreatefrompng($image->getContentFile()); break;
        }
        $thumb = imagecreatetruecolor(self::imageWidth, self::imageHeight);
        if (($params[2] == IMAGETYPE_PNG) || ($params[2] == IMAGETYPE_GIF)) {
            $transparencyIndex = imagecolortransparent($source);
            $transparencyColor = array('red' => self::backgroundRed, 'green' => self::backgroundGreen, 'blue' => self::backgroundBlue);
            if ($transparencyIndex >= 0) {
                $transparencyColor = imagecolorsforindex($source, $transparencyIndex);
            }
            $transparencyIndex = imagecolorallocate($thumb, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
            imagefill($thumb, 0, 0, $transparencyIndex);
            imagecolortransparent($thumb, $transparencyIndex);
        } else {
            $transparencyIndex = imagecolorallocate($thumb, self::backgroundRed, self::backgroundGreen, self::backgroundBlue);
            imagefill($thumb, 0, 0, $transparencyIndex);
        }
        $factor = max(self::imageWidth / $params[0], self::imageHeight / $params[1]);
        $factor = min($factor, 1);
        $srcsx = self::imageWidth / $factor;
        $srcsy = self::imageHeight / $factor;
        if ($srcsx > $params[0]) {
            $srcsx = $params[0];
        }
        if ($srcsy > $params[1]) {
            $srcsy = $params[1];
        }
        $newsx = $srcsx * $factor;
        $newsy = $srcsy * $factor;
        $srcx = ($params[0] - $srcsx) / 2;
        $srcy = ($params[1] - $srcsy) / 2;
        $newx = (self::imageWidth - $newsx) / 2;
        $newy = (self::imageHeight - $newsy) / 2;
        imagecopyresampled($thumb, $source, $newx, $newy, $srcx, $srcy, $newsx, $newsy, $srcsx, $srcsy);
        
        ob_start();
        imagejpeg($thumb, null, self::quality);
        $thumbString = ob_get_contents();
        ob_end_clean();
        
        $response = new Response($thumbString);
        $response->headers->replace(array('Content-type' => 'image/jpeg'));
        $response->setExpires(new \DateTime('+1 week'));
        return $response;
    }
    
}
