<?php
/**
 * Replacement template for [gallery] shortcode
 *
 * array $gallery [
 *      \Chronos\Models\Media $media
 *      string $alt
 *      string $title
 * ]
 */
?>
<div class="gallery-wrapper">
    <ul class="gallery">
        @foreach($gallery as $image)
        <li><img src="{{ $image->media->file }}" alt="{{ $image->alt }}" title="{{ $image->title }}" /></li>
        @endforeach
    </ul>
</div>