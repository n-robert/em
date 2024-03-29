<?php

namespace App\Http\Controllers;

use App\Contracts\ControllerInterface;
use App\Services\ReminderService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Inertia\Response as InertiaResponse;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Str;
use App\Services\PdfFormFillingService;
use App\Services\XmlFormHandlingService;

class BaseController extends Controller implements ControllerInterface
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $names;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var XmlFormHandlingService
     */
    protected $formHandlingService;

    /**
     * @var PdfFormFillingService
     */
    protected $formFillingService;

    /**
     * @var string
     */
    protected $customEditLink;

    /**
     * @var bool
     */
    protected $canCreateNewItem = true;

    /**
     * BaseController constructor.
     *
     * @param PdfFormFillingService $formFillingService
     * @param XmlFormHandlingService $formHandlingService
     * @param Request $request
     */
    public function __construct(
        PdfFormFillingService  $formFillingService,
        XmlFormHandlingService $formHandlingService,
        Request                $request
    )
    {
        $this->formFillingService = $formFillingService;
        $this->formHandlingService = $formHandlingService;
        $this->request = $request;
        $this->name = strtolower(
            str_replace('Controller', '', class_basename(static::class))
        );
        $this->names = Str::plural($this->name);

        $perPage = (int)$this->request->get('perPage') ?: 15;
        session(['perPage' => $perPage]);
    }

    /**
     * Handle dynamic method calls into the controller.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return
            method_exists($this, $method) ?
                $this->$method(...$parameters) :
                parent::__call($method, $parameters);
    }

    /**
     * Dynamically retrieve class field.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        $class = ucfirst($this->name);

        switch ($key) {
            case 'model':
                $class = 'App\\Models\\' . $class;
                break;
            case 'requestValidation':
                $class = 'App\\Http\\Requests\\' . $class . 'RequestValidation';
                break;
        }

        return app($class);
    }

    /**
     * Apply a filter from request.
     *
     * @return RedirectResponse|true
     */
    public function applyFilter()
    {
        $name = $this->request->input('name', '');
        $field = $this->request->input('field', '');
        $value = $this->request->input('value', '');
        $action = $this->request->input('action', '');

        if (!$field) {
            return true;
        }

        try {
            if (!$value || !$action) {
                throw new \Exception(__('Not enough parameters.'));
            }

            if (!in_array($action, ['remove', 'put'])) {
                throw new \Exception(__('Illegal action.'));
            }

            $key = $this->names . '.filters.';
            $key .= is_array($value) ? $field . '.' . $name : $field . '.' . $value;

            switch ($action) {
                case 'remove':
                    $args = [$key];
                    break;
                case 'put':
                    $args = [$key, $value];
                    break;
                default:
                    $args = [];
            }

            Session::$action(...$args);
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        session(['filtersModal' => true]);

        return redirect()->route('gets.' . $this->names);
    }

    /**
     * Delete a record.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete()
    {
        try {
            $id = $this->request->input('id');
            $this->model->find($id)->delete();
        } catch (\Exception $e) {
            abort(404, $e->getMessage());
        }

        return redirect()->route('gets.' . $this->names);
    }

    /**
     * Get form fields from XML-file.
     *
     * @param string $dir
     * @param string $name
     * @param int $id
     * @return array
     */
    public function getFormFields(string $dir, string $name, int $id): array
    {
        return call_user_func_array(
            [$this->formHandlingService, 'getFormFields'],
            [$dir, $name, $id]
        );
    }

    /**
     * Get item data.
     *
     * @param int|string $id
     * @return array
     */
    public function getItem($id): array
    {
        if (is_numeric($id)) {
            $item = $this->model->findOrFail($id);
        } else {
            $item = $this->model;
            $attributes = Schema::getColumnListing($this->model->names);

            foreach ($attributes as $attribute) {
                $item->setAttribute($attribute, null);
            }
        }

        $userIds = $item->user_ids ?: [Auth::id()];
        session(['user_ids' => $userIds]);

        $action = $id > 0 ? 'update' : 'store';
        $listUrl = URL::route('gets.' . $this->names, ['page' => session('page')], false);

        $formFields = call_user_func_array(
            [$this->formHandlingService, 'getFormFields'],
            ['system.item', $this->name, $id]
        );
        $requiredFields = $formFields['requiredFields'];
        unset($formFields['requiredFields']);

        return [
            'item'            => $item,
            'repeatable'      => $item->repeatable,
            'action'          => $action,
            'listUrl'         => $listUrl,
            'formFields'      => $formFields,
            'requiredFields'  => $requiredFields,
            'controllerName'  => $this->name,
            'controllerNames' => $this->names,
        ];
    }

    /**
     * Show items list.
     *
     * @param string $skippedField
     * @param bool $skip
     * @param array $selectedFilters
     * @return array
     */
    public function getItems(string $skippedField = '',
                             bool   $skip = true,
                             array  $selectedFilters = []): array
    {
        session(['views' => XmlFormHandlingService::getModelList(true)]);
        $query =
            $this->model
                ->applyFilters($selectedFilters)
                ->applyDefaultOrder()
                ->applySelectClauses()
                ->select($this->model->toSelect);

        if ($this->model->toSelectRaw) {
            $query->selectRaw($this->model->toSelectRaw);
        }

        if ($this->model->groupBy) {
            $query->groupBy($this->model->groupBy);
        }

        $items = $query->paginate(session('perPage'));
        $filters = $this->model->getFilters($skip, $skippedField);
        $hasFilters = count_array_recursive(session($this->names . '.filters'));
        $pagination = $this->model->getPagination($items);
        $modal = [$this->names => false];
        $docList = [];

        foreach ($items->all() as $item) {
            $key = $item->id ?: $item->default_name;
            $modal[$key] = false;
        }

        $modal['filters'] = session('filtersModal');
        session(['filtersModal' => false]);
        session(['page' => $this->request->input('page')]);

        call_user_func_array([$this->formHandlingService, 'checkDocList'], [$this->name, &$modal, &$docList]);

        $formFields = call_user_func_array(
            [$this->formHandlingService, 'getFormFields'],
            ['system.list', $this->names]
        );
        unset($formFields['requiredFields']);

        if ($visaExtensionReminder = ReminderService::visaExtensionReminder(false)) {
            $modal['visaExtensionReminder'] = true;
        }

        return [
            'items'                 => $items->all(),
            'filters'               => $filters,
            'hasFilters'            => $hasFilters,
            'pagination'            => $pagination,
            'modal'                 => $modal,
            'docList'               => $docList,
            'formFields'            => $formFields,
            'controllerName'        => $this->name,
            'controllerNames'       => $this->names,
            'canCreateNewItem'      => $this->canCreateNewItem,
            'visaExtensionReminder' => $visaExtensionReminder,
        ];
    }

    /**
     * Print WPM form.
     *
     * @param string $doc
     * @param int $id
     * @return void
     */
    public function printDoc(string $doc, int $id)
    {
        if (!Gate::allows('can-edit'))
            dd(__('Sorry, you are not authorized to access this page.'));

        $docData = $this->request->input();

        array_walk($docData, function (&$data) {
            $data = trim($data);
        });

        call_user_func_array(
            [$this->formFillingService, 'printDoc'],
            [$doc, $id, $docData]
        );
    }

    /**
     * Show item screen.
     *
     * @param int|string $id
     * @return InertiaResponse
     */
    public function show($id): InertiaResponse
    {
        $canEdit = Gate::allows('can-edit');
        $page = 'EM/Item';
        $page .= $canEdit ? 'Edit' : 'View';

        return Jetstream::inertia()
                        ->render(
                            $this->request,
                            $page,
                            $this->getItem($id)
                        );
    }

    /**
     * Show items list.
     *
     * @param string $skippedField
     * @param bool $skip
     * @param array $selectedFilters
     * @return InertiaResponse
     */
    public function showAll(string $skippedField = '',
                            bool   $skip = true,
                            array  $selectedFilters = []): InertiaResponse
    {
        $page = 'EM/Items';

        return Jetstream::inertia()
                        ->render(
                            $this->request,
                            $page,
                            $this->getItems($skippedField, $skip, $selectedFilters)
                        );
    }

    /**
     * Store new record.
     *
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        return $this->save();
    }

    /**
     * Bind request data and save model
     *
     * @param Model|null $model
     * @return RedirectResponse
     */
    public function save(Model $model = null): RedirectResponse
    {
        if (!Gate::allows('can-edit'))
            dd(__('Sorry, you are not authorized to access this page.'));

        if (!$model) $model = $this->model;

        $model
            ->fill($this->requestValidation->except('type'))
            ->save();

        return
            $this->request->input('type') == 'save' ?
                redirect()->route('gets.' . $this->names, ['page' => session('page')]) :
                redirect()->route('gets.' . $this->name, ['id' => $model->id]);
    }

    /**
     * Update existing record.
     *
     * @param Model $model
     * @return RedirectResponse
     */
    public function update(Model $model): RedirectResponse
    {
        return $this->save($model);
    }
}
