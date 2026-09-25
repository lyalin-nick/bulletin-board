<?php

namespace app\modules\admin\controllers;

use app\models\Category;
use app\modules\admin\searches\CategorySearch;
use app\rbac\Rbac;
use himiklab\sortablegrid\SortableGridAction;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends BaseAdminController
{
    public function actions(): array
    {
        return [
            'sort' => [
                'class' => SortableGridAction::class,
                'modelName' => Category::class,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Category models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        $this->view->params['breadcrumbs'] = $this->buildBreadcrumbs(category: $searchModel, lastUrl: false);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Category model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        $model = $this->findModel($id);

        $this->view->params['breadcrumbs'] = $this->buildBreadcrumbs(category: $model, lastUrl: false);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * @return string
     */
    public function actionTree(): string
    {
        $categories = Category::find()->orderBy(['sort_order' => SORT_ASC])->all();
        $byParent = [];
        foreach ($categories as $category) {
            $byParent[$category->parent_id ?? 0][] = $category;
        }

        $this->view->params['breadcrumbs'] = $this->buildBreadcrumbs(null);

        return $this->render('index-tree', ['byParent' => $byParent, 'parentId' => 0]);
    }

    /**
     * Creates a new Category model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     * @throws Exception
     */
    public function actionCreate(): Response|string
    {
        $model = new Category();


        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->parent_id = $this->request->get('parent_id');

            $model->loadDefaultValues();
        }

        $this->view->params['breadcrumbs'] = $this->buildBreadcrumbs($model);

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return string|Response
     * @throws Exception
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $this->view->params['breadcrumbs'] = $this->buildBreadcrumbs($model);

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Category
    {
        if (($model = Category::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * @param Category|null $category
     * @param bool $lastUrl
     * @return array
     */
    protected function buildBreadcrumbs(?Category $category, bool $lastUrl = true): array
    {
        $crumbs = [['label' => 'Категории', 'url' => ['index']]];

        foreach ($category?->getPath() ?? [] as $node) {
            if ($node->name) {
                $crumbs[] = [
                    'label' => $node->name,
                    'url' => ['index', 'CategorySearch' => ['parent_id' => $node->id]],
                ];
            }
        }

        if ($lastUrl === false) {
            unset($crumbs[array_key_last($crumbs)]['url']);
        }

        return $crumbs;
    }

    protected function accessRules(): array
    {
        return [
            [
                'allow' => true,
                'permissions' => [Rbac::PERM_MANAGE_CATEGORIES],
            ],
        ];
    }
}
