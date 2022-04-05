<?php

use Botble\Base\Models\MetaBox as MetaBoxModel;
use Botble\Blog\Models\Category;
use Botble\Blog\Models\Post;
use Botble\Blog\Repositories\Caches\PostCacheDecorator;
use Botble\Blog\Repositories\Interfaces\PostInterface;
use Illuminate\Support\Collection;
use Theme\Flow\Repositories\Eloquent\PostRepository;
use Botble\Member\Repositories\Interfaces\MemberInterface;
use Theme\Flow\Http\Requests\CustomPostRequest;
use Botble\Member\Models\Member;

app()->bind(PostInterface::class, function () {
    return new PostCacheDecorator(new PostRepository(new Post));
});

register_page_template([
    'default'        => __('Default'),
    'full'           => __('Full'),
    'homepage'       => __('Homepage'),
    'no-breadcrumbs' => __('No Breadcrumbs'),
    'right-sidebar'  => __('Right sidebar'),
]);

register_sidebar([
    'id'          => 'footer_sidebar_1',
    'name'        => __('Footer sidebar 1'),
    'description' => __('Sidebar in the footer of page'),
]);

register_sidebar([
    'id'          => 'footer_sidebar_2',
    'name'        => __('Footer sidebar 2'),
    'description' => __('Sidebar in the footer of page'),
]);

RvMedia::setUploadPathAndURLToPublic();
RvMedia::addSize('medium', 800, 800)
    ->addSize('small', 540, 320);

if (!function_exists('query_post')) {
    /**
     * @param int $limit
     * @param array $with
     * @return Collection
     */
    function query_post($params)
    {
        $filters = [
            'limit'              => empty($params['limit']) ? 10 : $params['limit'],
            'categories'         => empty($params['categories']) ? null : explode(',', $params['categories']),
            'categories_exclude' => empty($params['categories_exclude']) ? null : explode(
                ',',
                $params['categories_exclude']
            ),
            'exclude'            => empty($params['exclude']) ? null : explode(',', $params['exclude']),
            'include'            => empty($params['include']) ? null : explode(',', $params['include']),
            'order_by'           => empty($params['order_by']) ? 'updated_at' : $params['order_by'],
        ];
        return app(PostInterface::class)->getFilters($filters);
    }
}

if (is_plugin_active('blog')) {
    add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function ($form, $data) {
        if (get_class($data) == Post::class) {
            $authors = app()->make(MemberInterface::class)
                    ->allBy([]);

            $authorsArray = [];
            foreach ($authors as $author) {
                $authorsArray[$author->id] = $author->getFullName();
            }

            $form
                    ->setValidatorClass(CustomPostRequest::class)
                    ->addAfter('status', 'author_id', 'customSelect', [
                        'label'      => __('Author'),
                        'label_attr' => ['class' => 'control-label required'],
                        'attr'       => [
                            'placeholder' => __('Select an author...'),
                        ],
                        'choices'    => $authorsArray,
                    ]);
        }

        return $form;
    }, 127, 2);

    add_action(BASE_ACTION_AFTER_CREATE_CONTENT, function ($type, $request, $object) {
        if (in_array($type, [POST_MODULE_SCREEN_NAME])) {
            $object->author_id = $request->input('author_id');
            $object->author_type = Member::class;
            $object->save();
        }
    }, 123, 3);

    add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, function ($type, $request, $object) {
        if (in_array($type, [POST_MODULE_SCREEN_NAME])) {
            $object->author_id = $request->input('author_id');
            $object->author_type = Member::class;
            $object->save();
        }
    }, 123, 3);
    add_action(BASE_ACTION_META_BOXES, function ($context, $object) {
        if (get_class($object) == Category::class && $context == 'side') {
            MetaBox::addMetaBox('additional_blog_category_fields', __('Addition Information'), function () {
                $image = null;
                $args = func_get_args();
                if (!empty($args[0])) {
                    $image = MetaBox::getMetaData($args[0], 'image', true);
                }

                return Theme::partial('blog-category-fields', compact('image'));
            }, get_class($object), $context);
        }
    }, 24, 2);

    add_action(BASE_ACTION_AFTER_CREATE_CONTENT, function ($type, $request, $object) {
        if (get_class($object) == Category::class) {
            MetaBox::saveMetaBoxData($object, 'image', $request->input('image'));
        }
    }, 230, 3);

    add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, function ($type, $request, $object) {
        if (get_class($object) == Category::class) {
            MetaBox::saveMetaBoxData($object, 'image', $request->input('image'));
        }
    }, 231, 3);

    add_action(BASE_ACTION_META_BOXES, 'add_addition_fields_in_post_screen', 30, 3);

    function add_addition_fields_in_post_screen($context, $object)
    {
        if (get_class($object) == Post::class && $context == 'top') {
            MetaBox::addMetaBox(
                'additional_post_fields',
                __('Addition Information'),
                function () {
                    $titleLayout = null;
                    $timeToRead = null;
                    $args = func_get_args();
                    if (!empty($args[0])) {
                        $titleLayout = MetaBox::getMetaData($args[0], 'title_layout', true);
                        $timeToRead = MetaBox::getMetaData($args[0], 'time_to_read', true);
                    }

                    return Theme::partial('blog-post-fields', compact('titleLayout', 'timeToRead'));
                },
                get_class($object),
                $context
            );
        }
    }

    add_action(BASE_ACTION_AFTER_CREATE_CONTENT, 'save_addition_post_fields', 230, 3);
    add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, 'save_addition_post_fields', 231, 3);

    function save_addition_post_fields($type, $request, $object)
    {
        if (is_plugin_active('blog') && get_class($object) == Post::class) {
            MetaBox::saveMetaBoxData($object, 'title_layout', $request->input('title_layout'));

            MetaBox::saveMetaBoxData($object, 'time_to_read', $request->input('time_to_read'));
        }
    }
}

app()->booted(function () {
    if (is_plugin_active('blog')) {
        Category::resolveRelationUsing('image', function ($model) {
            return $model->morphOne(MetaBoxModel::class, 'reference')->where('meta_key', 'image');
        });
    }
});

if (is_plugin_active('ads')) {
    AdsManager::registerLocation('panel-ads', __('Panel Ads'))
        ->registerLocation('top-sidebar-ads', __('Top Sidebar Ads'))
        ->registerLocation('bottom-sidebar-ads', __('Bottom Sidebar Ads'));
}


Form::component('themeIcon', Theme::getThemeNamespace() . '::partials.icons-field', [
    'name',
    'value'      => null,
    'attributes' => [],
]);

if (!function_exists('get_category_title_layout')) {
    /**
     * @return array
     */
    function get_category_title_layout(): array
    {
        return [
            'layout-1' => __('Layout 1'),
            'layout-2' => __('Layout 2'),
        ];
    }
}

if (!function_exists('get_single_title_templates')) {
    /**
     * @return array
     */
    function get_single_title_layout(): array
    {
        return [
            'default'  => __('Default'),
            'top-full' => __('Top Full'),
            'inline'   => __('Inline'),
        ];
    }
}

if (!function_exists('get_sidebar_layout')) {
    /**
     * @return array
     */
    function get_sidebar_layout(): array
    {
        return [
            'default'       => __('Default'),
            'left-sidebar'  => __('Left sidebar'),
            'right-sidebar' => __('Right sidebar'),
        ];
    }
}

if (!function_exists('get_related_style')) {
    /**
     * @return array
     */
    function get_related_style(): array
    {
        return [
            'default' => __('Default'),
            'popup'   => __('Popup'),
        ];
    }
}

if (!function_exists('display_ad')) {
    /**
     * @param string $location
     * @param array $attributes
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    function display_ad(string $location, array $attributes = []): string
    {
        if (!is_plugin_active('ads')) {
            return '';
        }

        return AdsManager::display($location, $attributes);
    }
}

if (!function_exists('random_color')) {
    /**
     * @return string
     */
    function random_color()
    {
        $colors = ['brand-1', 'warning', 'primary', 'info', 'success', 'danger'];

        return 'bg-' . $colors[array_rand($colors)];
    }
}

if (!function_exists('get_time_to_read')) {
    /**
     * @param Post $post
     * @return string
     */
    function get_time_to_read(Post $post)
    {
        $timeToRead = MetaBox::getMetaData($post, 'time_to_read', true);

        if ($timeToRead) {
            return number_format($timeToRead);
        }

        return number_format(strlen(strip_tags($post->content)) / 300);
    }
}
if (!function_exists('comment_object_enable')) {
    function comment_object_enable($object) {
        $comment_status = $object->getMetaData('comment_status', true);
        return  setting('comment_enable') && ($comment_status == 1 || $comment_status == '') &&
        in_array(get_class($object), json_decode(setting('comment_menu_enable', '[]'), true));
    }
}
