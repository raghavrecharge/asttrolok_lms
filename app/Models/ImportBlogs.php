<?php
namespace App\Models;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Translation\BlogTranslation;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportBlogs implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
         
        $blog = Blog::create([
            'slug' => $row[2],
            'category_id' => '40',
            'author_id' => '1',
            'image' => '/store/1/oldblog/'.$row[3],
            'enable_comment' => '1',
            'status' => 'publish',
            'created_at' => time(),
            'updated_at'     => time(),
        ]);
        if ($blog) {
            BlogTranslation::updateOrCreate([
                'blog_id' => $blog->id,
                'locale' => 'en',
            ], [
                'title' => $row[1],
                'description' => $row[1],
                'meta_description' => $row[1],
                'content' => $row[0],
            ]);

            if ($blog->status == 'publish' and $blog->author_id != auth()->id()) {
                $notifyOptions = [
                    '[blog_title]' => $blog->title,
                ];
               return sendNotification('publish_instructor_blog_post', $notifyOptions, $blog->author_id);
            }
        }
           
    }
}
