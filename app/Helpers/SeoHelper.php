<?php

namespace App\Helpers;

use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\TwitterCard;

class SeoHelper
{
    public static function set($data = [])
    {
        // Basic
        SEOMeta::setTitle($data['title'] ?? config('app.name'));
        SEOMeta::setDescription($data['description'] ?? '');
        SEOMeta::setCanonical($data['canonical'] ?? url()->current());

        // OpenGraph
        OpenGraph::setTitle($data['og_title'] ?? $data['title'] ?? '');
        OpenGraph::setDescription($data['og_description'] ?? $data['description'] ?? '');
        OpenGraph::setUrl(url()->current());
        OpenGraph::addProperty('type', 'website');

        if (!empty($data['image'])) {
            OpenGraph::addImage($data['image']);
        }

        // Twitter
        TwitterCard::setTitle($data['twitter_title'] ?? $data['title'] ?? '');
        TwitterCard::setDescription($data['twitter_description'] ?? $data['description'] ?? '');

        if (!empty($data['image'])) {
            TwitterCard::setImage($data['image']);
        }
    }
}
