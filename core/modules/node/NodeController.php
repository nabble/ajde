<?php

class NodeController extends Ajde_Controller
{
    /**
     * @var NodeModel
     */
    protected $node;

    public function getCanonicalUrl()
    {
        if ($this->node->hasLoaded()) {
            return config('i18n.enabled') ?
                Ajde_Lang::getInstance()->getShortLang().'/'.$this->node->getSlug() :
                $this->node->getSlug();
        }

        return '';
    }

    public function beforeInvoke()
    {
        return true;
    }

    public function view()
    {
        // we want to display published nodes only
        if (!(UserModel::getLoggedIn() && UserModel::getLoggedIn()->isAdmin())) {
            Ajde::app()->getRequest()->set('filterPublished', true);
        }

        // get the current slug
        $slug = $this->getSlug();

        $node = new NodeModel();
        $node->loadBySlug($slug);
        $this->node = $node;

        if ($node->checkPublished() === false) {
            Ajde_Dump::warn('Previewing unpublished node');
        }

        // check if we have a hit
        if (!$node->hasLoaded()) {
            Ajde::app()->getResponse()->redirectNotFound();
        }

        Ajde_Event::trigger($this, 'onAfterNodeLoaded', [$node]);

        // update cache
        Ajde_Cache::getInstance()->updateHash($node->hash());
        Ajde_Cache::getInstance()->updateHash($node->getChildren()->hash());
        Ajde_Cache::getInstance()->addLastModified(strtotime($node->updated));

        // set title
        if (!Ajde::app()->getDocument()->hasNotEmpty('title')) {
            Ajde::app()->getDocument()->setTitle($node->getTitle());
        }
        // set summary
        if ($node->summary) {
            Ajde::app()->getDocument()->setDescription($node->summary);
        }
        // set author
        $node->loadParent('user');
        /** @var UserModel $owner */
        $owner = $node->getUser();
        Ajde::app()->getDocument()->setAuthor($owner->getFullname());

        // set template
        $nodetype = $node->getNodetype();
        $action = str_replace(' ', '_', strtolower($nodetype->get($nodetype->getDisplayField())));
        $this->setAction($action);

        // featured image
        if ($image = $node->featuredImage()) {
            Ajde::app()->getDocument()->setFeaturedImage($image);
        }

        // pass node to document, only first
        $layout = Ajde::app()->getDocument()->getLayout();
        if (!$layout->hasAssigned('node')) {
            $layout->assign('node', $node);
        }

        // pass node to view
        $this->getView()->assign('node', $node);

        // render the temnplate
        return $this->render();
    }

    public static function getNodeOptions()
    {
        // show only
        $showOnlyWhenFields = [
            'title',
            'subtitle',
            'content',
            'summary',
            'media',
            'tag',
            'additional_media',
            'children',
            'published',
            'related_nodes',
        ];
        $showOnlyWhen = [];
        $nodetypes = new NodetypeCollection();
        foreach ($nodetypes as $nodetype) {
            foreach ($showOnlyWhenFields as $field) {
                if (!isset($showOnlyWhen[$field])) {
                    $showOnlyWhen[$field] = [];
                }
                if ($nodetype->get($field) == 1) {
                    $showOnlyWhen[$field][] = $nodetype->getPK();
                }
            }
        }

        $options = new Ajde_Crud_Options();
        $options
            ->selectFields()
                ->selectField('slug')
                    ->setHelp('Changing the slug might impact SEO scores and break existing internal links')
                    ->up()
                ->selectField('nodetype')
                    ->setOrderBy('sort')
                    ->setIsRequired(false)
                    ->up()
                ->selectField('title')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['title'])
                    ->setFunction('displayTreeName')
                    ->setEmphasis(true)
                    ->up()
                ->selectField('subtitle')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['subtitle'])
                    ->up()
                ->selectField('content')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['content'])
                    ->up()
                ->selectField('summary')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['summary'])
                    ->setDisableRichText(true)
                    ->up()
                ->selectField('media')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['media'])
                    ->setShowLabel(false)
                    ->setUsePopupSelector(true)
                    ->setListRoute('admin/media:view.crud')
                    ->setUseImage(true)
                    ->addTableFileField('thumbnail', UPLOAD_DIR)
                    ->setThumbDim(300, 300)
                    ->up()
                ->selectField('tag')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['tag'])
                    ->setType('multiple')
                    ->setEditRoute('admin/tag:view.crud')
                    ->setThumbDim(30, 30)
                    ->setShowLabel(false)
                    ->setCrossReferenceTable('node_tag')
                    ->setSimpleSelector(true)
                    ->up()
                ->selectField('parent')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['children'])
                    ->setType('fk')
                    ->setModelName('node')
                    ->setShowLabel(true)
                    ->setUsePopupSelector(true)
                    ->setListRouteFunction('listRouteParent')
                    ->up()
                ->selectField('published')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['published'])
                    ->setFunction('displayPublished')
                    ->setType('boolean')
                    ->up()
                ->selectField('published_start')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['published'])
                    ->up()
                ->selectField('published_end')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['published'])
                    ->up()
                ->selectField('sort')
                    ->setType('sort')
                    ->up()
                ->selectField('additional_media')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['additional_media'])
                    ->setType('multiple')
                    ->setEditRoute('admin/media:view.crud')
                    ->addTableFileField('thumbnail', UPLOAD_DIR)
                    ->setHideMainColumn(true)
                    ->setUsePopupSelector(true)
                    ->setListRoute('admin/media:view.crud')
                    ->setModelName('media')
                    ->setThumbDim(100, 100)
                    ->addSortField('sort')
                    ->setShowLabel(false)
                    ->setCrossReferenceTable('node_media')
                    ->up()
                ->selectField('children')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['children'])
                    ->setModelName('node')
                    ->setParent('parent')
                    ->setHideInIframe(true)
                    ->setType('multiple')
                    ->setEditRouteFunction('editRouteChild')
                    ->addTableField('nodetype')
                    ->addSortField('sort')
                    ->setShowLabel(false)
                    ->up()
                ->selectField('related_nodes')
                    ->addShowOnlyWhen('nodetype', $showOnlyWhen['related_nodes'])
                    ->setType('multiple')
                    ->setEditRoute('admin/node:view.crud')
                    ->setUsePopupSelector(true)
                    ->setListRoute('admin/node:view.crud')
                    ->setModelName('node')
                    ->setSimpleSelector(true)
                    ->addSortField('sort')
                    ->setShowLabel(false)
                    ->setChildField('related')
                    ->setCrossReferenceTable('node_related')
                    ->up()
                ->selectField('added')
                    ->setIsReadonly(true)
                    ->up()
                ->selectField('updated')
                    ->setFunction('displayAgo')
                    ->setIsReadonly(true)
                    ->up()
                ->selectField('lang')
                    ->setFunction('displayLang')
                    ->setType('i18n')
                    ->setCloneFields([
                        'nodetype',
                        'media',
                    ])
                    ->up()
                ->up()
            ->selectList()
                ->selectButtons()
                    ->setNew(true)
                    ->setEdit(true)
                    ->setView(true)
                    ->setViewUrlFunction('getSlug')
                    //			->addItemButton('view', 'view')
                    ->addItemButton('child', 'addChildButton', 'btn-success add-child', false, true)
                    ->up()
                ->setMain('title')
                    ->setShow(['title', 'lang', 'updated', 'published', 'sort'])
                    ->setThumbDim(50, 50)
                    ->setSearch(true)
                    ->selectView()
                    ->setMainFilter('nodetype')
                    ->setMainFilterGrouper('category')
                    ->setOrderBy('sort')
                    ->up()
                    //				->setPanelFunction('displayPanel')
                ->up()
            ->selectEdit()
                ->selectLayout()
                    ->addRow()
                        ->addColumn()
                            ->setSpan(8)
                            ->addBlock()
                                ->setShow(['title', 'subtitle', 'content', 'summary'])
                                ->up()
                            ->addBlock()
                                ->setClass('meta')
                                ->up()
                            ->addBlock()
                                ->setClass('')
                                ->setTitle('Child nodes')
                                ->setShow(['children'])
                                ->up()
                            ->up()
                        ->addColumn()
                            ->setSpan(4)
                            ->addBlock()
                                ->setTitle('Featured image')
                                ->setClass('sidebar')
                                ->setShow(['media'])
                                ->up()
                            ->addBlock()
                                ->setTitle('Tags')
                                ->setClass('sidebar')
                                ->setShow(['tag'])
                                ->up()
                            ->addBlock()
                                ->setClass('sidebar')
                                ->setTitle('Additional media')
                                ->setShow(['additional_media'])
                                ->up()
                            ->addBlock()
                                ->setClass('sidebar')
                                ->setTitle('Related nodes')
                                ->setShow(['related_nodes'])
                                ->up()
                            ->addBlock()
                                ->setTitle('Node status')
                                ->setClass('well left')
                                ->setShow(['slug', 'published', 'published_start', 'published_end'])
                                ->up()
                            ->addBlock()
                                ->setTitle('Metadata')
                                ->setClass('well left')
                                ->setShow(['added', 'updated', 'parent', 'user', 'lang'])
        ->finished();

        /* @var $decorator Ajde_Crud_Cms_Meta_Decorator */
        $decorator = new Ajde_Crud_Cms_Meta_Decorator();
        $decorator->setActiveBlock(1);
        $decorator->setOptions($options);
        $decorator->decorateInputs('nodetype_meta', 'nodetype', 'sort', 'nodetype', [
            new Ajde_Filter_Where('target', Ajde_Filter::FILTER_EQUALS, 'node'),
        ]);

        if (Ajde::app()->getRequest()->has('new')) {
            // set owner
            $user = UserModel::getLoggedIn();
            $options->selectFields()->selectField('user')->setValue($user->getPK())->finished();
            $options->selectFields()->selectField('slug')->setIsReadonly(true)->finished();

            if (!UserModel::isAdmin()) {
                $currentUser = UserModel::getLoggedIn();
                $subquery = '(SELECT user_node.user FROM user_node WHERE user_node.node IN (SELECT user_node.node FROM user_node WHERE user_node.user = '.(int) $currentUser->getPK().' GROUP BY user_node.node))';
                $userFilters = [
                    new Ajde_Filter_Where('user.id', Ajde_Filter::FILTER_IN, new Ajde_Db_Function($subquery)),
                ];
                $options->selectFields()->selectField('user')->setAdvancedFilter($userFilters);
            }
        }
        if (Ajde::app()->getRequest()->has('edit')) {
            if (!UserModel::isAdmin()) {
                $options->selectFields()->selectField('user')
                    ->setIsReadonly(true)
                    ->setUsePopupSelector(true)
                    ->finished();
            }
        }

        return $options;
    }
}
