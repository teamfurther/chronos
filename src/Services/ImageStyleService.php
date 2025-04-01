<?php

namespace Chronos\Services;

use Chronos\Models\Media;
use Chronos\Models\ImageStyle;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageStyleService
{
    public static function checkIfGeneratableImageStyle($basename)
    {
        $extension = pathinfo($basename, PATHINFO_EXTENSION);

        // check if image
        if (!in_array($extension, Media::$image_types))
            return false;

        // check if image style
        $media = Media::where('basename', $basename)->first();

        if (!$media)
            return false;

        return true;
    }

    public static function generate($upload_path, $asset_path, $basename)
    {
        $media = Media::where('basename', $basename)->first();
        $style = $media->style;

        $manager = new ImageManager(new Driver());
        $image = $manager->read($upload_path . '/' .$media->parent->basename);

        // resize
        if ($style->height || $style->width) {
            if ($style->upsizing) {
                $image->scale($style->width, $style->height);
            } else {
                $image->scaleDown($style->width, $style->height);
            }
        }

        // rotate
        if ($style->rotate != 0) {
            $image->rotate($style->rotate);
        }

        // crop
        if ($style->crop_height && $style->crop_width) {
            if ($style->crop_type == 'fit') {
                if ($style->upsizing) {
                    $image->cover($style->crop_width, $style->crop_height);
                } else {
                    $image->coverDown($style->crop_width, $style->crop_height);
                }
            } else {
                $crop_x = null;
                $crop_y = null;

                switch ($style->anchor_h) {
                    case 'left':
                        $crop_x = 0;
                        break;
                    case 'center':
                        $crop_x = $image->width() / 2 - $style->crop_width / 2;
                        break;
                    case 'right':
                        $crop_x = $image->width() - $style->crop_width;
                        break;
                }

                switch ($style->anchor_h) {
                    case 'top':
                        $crop_y = 0;
                        break;
                    case 'middle':
                        $crop_y = $image->height() / 2 - $style->crop_height / 2;
                        break;
                    case 'bottom':
                        $crop_y = $image->height() - $style->crop_height;
                        break;
                }

                $image->crop($style->crop_width, $style->crop_height, $crop_x, $crop_y);
            }
        }

        // greyscale
        if ($style->greyscale)
            $image->greyscale();

        // save
        $filepath = $upload_path . '/' . $basename;
        $image->save($filepath, 80);

        // update media model
        $file_url = $asset_path . '/' . $basename;

        $media = Media::where('file', $file_url)->first();
        $media->size = $image->filesize();
        $media->image_height = $image->height();
        $media->image_width = $image->width();
        $media->save();

        return true;
    }

    public static function make($asset_path, $filename, $extension, $parent)
    {
        $q = ImageStyle::query()->withoutGlobalScope('uncloaked');
        $image_styles = $q->get();

        foreach ($image_styles as $image_style) {
            $style_filename = $filename . '-' . Str::slug($image_style->name);
            $style_basename = $style_filename . '.' . $extension;

            // create media model
            $file_url = $asset_path . '/' . $style_basename;
            Media::create([
                'parent_id' => $parent->id,
                'file' => $file_url,
                'filename' => $style_filename,
                'basename' => $style_basename,
                'type' => $extension,
                'image_style_id' => $image_style->id
            ]);
        }
    }

}