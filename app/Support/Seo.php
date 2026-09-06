<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class Seo
{
    /**
     * @param  array<int, array{name: string, href: string}>  $breadcrumbs
     * @param  array<int, array<string, mixed>>  $schemas
     * @return array<string, mixed>
     */
    public static function page(
        Request $request,
        string $title,
        string $description,
        array $breadcrumbs = [],
        array $schemas = [],
        ?string $image = null,
        string $type = 'website',
        bool $noindex = false,
    ): array {
        if ($breadcrumbs !== []) {
            $schemas[] = self::breadcrumbSchema($breadcrumbs);
        }

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $request->url(),
            'image' => $image ? self::absolute($image) : self::absolute('/images/logo/logo_paulinas.png'),
            'type' => $type,
            'noindex' => $noindex,
            'breadcrumbs' => $breadcrumbs,
            'schemas' => $schemas,
        ];
    }

    /** @return array<string, mixed> */
    public static function organization(): array
    {
        $sameAs = array_values(array_filter(config('paulinas.social')));

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('paulinas.legal_name'),
            'alternateName' => config('paulinas.short_name'),
            'url' => self::absolute('/'),
            'logo' => self::absolute('/images/logo/logo_paulinas.png'),
            'telephone' => config('paulinas.contact.phone'),
            'email' => config('paulinas.contact.sales_email'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => config('paulinas.address.street'),
                'addressLocality' => config('paulinas.address.commune'),
                'addressRegion' => config('paulinas.address.city'),
                'addressCountry' => 'CL',
            ],
            'sameAs' => $sameAs !== [] ? $sameAs : null,
        ], fn (mixed $value): bool => $value !== null);
    }

    /** @return array<string, mixed> */
    public static function product(Product $product): array
    {
        $images = $product->images->map(fn ($image): string => self::absolute($image->url))->all();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->title,
            'description' => $product->short_description ?? $product->description,
            'image' => $images !== [] ? $images : null,
            'isbn' => $product->isbn,
            'brand' => $product->publisher ? ['@type' => 'Brand', 'name' => $product->publisher->name] : null,
            'offers' => [
                '@type' => 'Offer',
                'url' => route('books.show', $product),
                'priceCurrency' => config('paulinas.currency.code'),
                'price' => $product->price,
                'availability' => $product->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ],
        ], fn (mixed $value): bool => $value !== null);
    }

    /** @return array<string, mixed>|null */
    public static function book(Product $product): ?array
    {
        if ($product->isbn === null) {
            return null;
        }

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Book',
            'name' => $product->title,
            'isbn' => $product->isbn,
            'author' => $product->authors->map(fn ($author): array => [
                '@type' => 'Person',
                'name' => $author->name,
                'url' => route('authors.show', $author),
            ])->all(),
            'publisher' => $product->publisher ? ['@type' => 'Organization', 'name' => $product->publisher->name] : null,
            'numberOfPages' => $product->pages,
            'datePublished' => $product->published_at?->toDateString(),
        ], fn (mixed $value): bool => $value !== null && $value !== []);
    }

    /** @return array<string, mixed> */
    public static function localBusiness(Branch $branch): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'BookStore',
            'name' => $branch->name,
            'url' => route('branches.show', $branch),
            'telephone' => $branch->phone,
            'email' => $branch->email,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $branch->address,
                'addressLocality' => $branch->commune,
                'addressRegion' => $branch->region,
                'addressCountry' => 'CL',
            ],
            'geo' => $branch->latitude !== null && $branch->longitude !== null ? [
                '@type' => 'GeoCoordinates',
                'latitude' => $branch->latitude,
                'longitude' => $branch->longitude,
            ] : null,
        ], fn (mixed $value): bool => $value !== null);
    }

    /** @return array<string, mixed> */
    public static function article(Post $post): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->excerpt,
            'image' => $post->image ? self::absolute(Storage::disk('public')->url($post->image)) : null,
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'mainEntityOfPage' => route('posts.show', $post),
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('paulinas.legal_name'),
                'logo' => ['@type' => 'ImageObject', 'url' => self::absolute('/images/logo/logo_paulinas.png')],
            ],
        ], fn (mixed $value): bool => $value !== null);
    }

    /** @param array<int, array{name: string, href: string}> $items */
    private static function breadcrumbSchema(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn (array $item, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => self::absolute($item['href']),
            ])->all(),
        ];
    }

    public static function absolute(string $url): string
    {
        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://') ? $url : url($url);
    }
}
