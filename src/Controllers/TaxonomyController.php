<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Models\Term;
use OOPress\Models\Taxonomy;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class TaxonomyController
{
    private Engine $view;
    
    public function __construct()
    {
        $this->view = new Engine(__DIR__ . '/../../views');
    }
    
    private function checkAdminAccess(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    // Categories
    public function categories(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $taxonomy = Taxonomy::firstWhere(['slug' => 'category']);
        $categories = [];
        
        if ($taxonomy) {
            $categories = Term::where(['taxonomy_id' => $taxonomy->id]);
        }
        
        $content = $this->view->render('admin/taxonomies/categories', [
            'title' => __('Manage Categories'),
            'categories' => $categories,
            'error' => null
        ]);
        
        return new Response($content);
    }
    
    public function createCategory(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        if ($request->method() === 'POST') {
            $name = trim($request->input('name'));
            $slug = $this->createSlug($name);
            $description = trim($request->input('description'));
            
            // Get or create category taxonomy
            $taxonomy = Taxonomy::firstWhere(['slug' => 'category']);
            if (!$taxonomy) {
                $taxonomy = new Taxonomy([
                    'name' => 'Category',
                    'slug' => 'category',
                    'description' => 'Post categories',
                    'hierarchical' => 1
                ]);
                $taxonomy->save();
            }
            
            $term = new Term([
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'taxonomy_id' => $taxonomy->id
            ]);
            
            if ($term->save()) {
                return Response::redirect('/admin/categories');
            }
            
            $error = __('Failed to create category');
        }
        
        return Response::redirect('/admin/categories');
    }
    
    public function editCategory(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $id = (int)$request->attribute('id');
        $category = Term::find($id);
        
        if (!$category) {
            return new Response('Category not found', 404);
        }
        
        if ($request->method() === 'POST') {
            $category->name = trim($request->input('name'));
            $category->description = trim($request->input('description'));
            
            if ($category->save()) {
                return Response::redirect('/admin/categories');
            }
            
            $error = __('Failed to update category');
        }
        
        $content = $this->view->render('admin/taxonomies/edit-category', [
            'title' => __('Edit Category'),
            'category' => $category,
            'error' => $error ?? null
        ]);
        
        return new Response($content);
    }
    
    public function deleteCategory(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $id = (int)$request->attribute('id');
        $category = Term::find($id);
        
        if ($category) {
            $category->delete();
        }
        
        return Response::redirect('/admin/categories');
    }
    
    // Tags
    public function tags(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $taxonomy = Taxonomy::firstWhere(['slug' => 'tag']);
        $tags = [];
        
        if ($taxonomy) {
            $tags = Term::where(['taxonomy_id' => $taxonomy->id]);
        }
        
        $content = $this->view->render('admin/taxonomies/tags', [
            'title' => __('Manage Tags'),
            'tags' => $tags,
            'error' => null
        ]);
        
        return new Response($content);
    }
    
    public function createTag(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        if ($request->method() === 'POST') {
            $name = trim($request->input('name'));
            $slug = $this->createSlug($name);
            $description = trim($request->input('description'));
            
            $taxonomy = Taxonomy::firstWhere(['slug' => 'tag']);
            if (!$taxonomy) {
                $taxonomy = new Taxonomy([
                    'name' => 'Tag',
                    'slug' => 'tag',
                    'description' => 'Post tags',
                    'hierarchical' => 0
                ]);
                $taxonomy->save();
            }
            
            $term = new Term([
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'taxonomy_id' => $taxonomy->id
            ]);
            
            if ($term->save()) {
                return Response::redirect('/admin/tags');
            }
        }
        
        return Response::redirect('/admin/tags');
    }
    
    public function editTag(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $id = (int)$request->attribute('id');
        $tag = Term::find($id);
        
        if (!$tag) {
            return new Response('Tag not found', 404);
        }
        
        if ($request->method() === 'POST') {
            $tag->name = trim($request->input('name'));
            $tag->description = trim($request->input('description'));
            
            if ($tag->save()) {
                return Response::redirect('/admin/tags');
            }
            
            $error = __('Failed to update tag');
        }
        
        $content = $this->view->render('admin/taxonomies/edit-tag', [
            'title' => __('Edit Tag'),
            'tag' => $tag,
            'error' => $error ?? null
        ]);
        
        return new Response($content);
    }
    
    public function deleteTag(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $id = (int)$request->attribute('id');
        $tag = Term::find($id);
        
        if ($tag) {
            $tag->delete();
        }
        
        return Response::redirect('/admin/tags');
    }
    
    private function createSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        $original = $slug;
        $counter = 1;
        while (Term::firstWhere(['slug' => $slug])) {
            $slug = $original . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}