<?php

namespace Drupal\node_event_form_ext\Service;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Drupal\Core\File\FileSystemInterface;
use Exception;

class QRCodeGenerator
{
    protected $qrcodeGenerator;

    public function __construct()
    {
        $this->qrcodeGenerator = new Writer(new ImageRenderer(
            new RendererStyle(400),
            new ImagickImageBackEnd()
        ));
    }

    public function generateQrCode(string $content)
    {
        $qrCodePath = $this->getQRCodeFilePath();
        try {
            $this->qrcodeGenerator->writeFile($content, $qrCodePath);
            return \Drupal::service('file_url_generator')->generate($qrCodePath)->toString();
        } catch (Exception $e) {
            \Drupal::logger('QR Code Generator Service')->error($e->getMessage(), $e->getTrace());
        }
    }

    private function getQRCodeFilePath()
    {
        $qrCodeDestination = 'public://assets';
        $fileSystem = \Drupal::service('file_system');
        $fileSystem->prepareDirectory($qrCodeDestination, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
        return $qrCodeDestination . '/' . $this->randomString() . '.png';
    }
    private function randomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
