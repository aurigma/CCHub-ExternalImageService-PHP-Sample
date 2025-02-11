<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\FileNotFoundException;
use app\Services\ImageService;


class FreeImagesController extends Controller
{

    private $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function imagesGetContent($id)
    {
        try {

            $this->validateId($id);
            $result = $this->imageService->getPreviewFile($id);

            return response()->file($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (FileNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    private function validateId($id)
    {
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $id)) {
            throw new \InvalidArgumentException('Invalid ID format');
        }
    }
}
