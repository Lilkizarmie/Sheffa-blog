<?php

use Botble\Media\Models\MediaFile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Botble\Blog\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Botble\Member\Models\Member;

class MigrateAuthorToMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('authors')) {
            $authors = DB::table('authors')->get();
            foreach ($authors as $author) {
                $member = Member::updateOrCreate(['email' => $author->email], [
                    'first_name' => '',
                    'last_name'  => $author->name,
                    'avatar_id'  => MediaFile::where('url', $author->avatar)->value('id'),
                    'password'   => bcrypt(time() . Str::random(20)),
                ]);

                $member->confirmed_at = $author->created_at;
                $member->save();

                $posts = Post::where('author_id', $author->id)
                    ->where('author_type', 'Botble\Author\Models\Author')
                    ->get();

                foreach ($posts as $post) {
                    $post->author_id = $member->id;
                    $post->author_type = Member::class;
                    $post->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Post::where('author_type', 'Botble\Member\Models\Member')
            ->update(['author_type' => 'Botble\Author\Models\Author']);
    }
}
