<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LookupTableController extends Controller
{
    protected string $lookupKey = '';

    public function index()
    {
        $config = $this->lookupConfig();
        $records = DB::table($config['table'])->get();

        return view($config['views']['index'], $this->viewData($config, 'index', [
            $config['variables']['index'] => $records,
        ]));
    }

    public function create()
    {
        $config = $this->lookupConfig();

        return view($config['views']['create'], $this->viewData($config, 'create'));
    }

    public function insert(Request $request)
    {
        $config = $this->lookupConfig();

        DB::table($config['table'])->insert($this->payload($config, $request, true));

        return $this->redirectWithMessage($config, 'store', $request);
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $config = $this->lookupConfig();
        $record = $this->findRecord($config, $id);

        return view($config['views']['edit'], $this->viewData($config, 'edit', [
            $config['variables']['item'] => $record,
        ]));
    }

    public function updates(Request $request, $id)
    {
        $config = $this->lookupConfig();

        DB::table($config['table'])
            ->where($config['primary_key'], $id)
            ->update($this->payload($config, $request));

        return $this->redirectWithMessage($config, 'update', $request);
    }

    public function deletes($id)
    {
        $config = $this->lookupConfig();
        $record = $this->findRecord($config, $id);

        return view($config['views']['delete'], $this->viewData($config, 'delete', [
            $config['variables']['item'] => $record,
        ]));
    }

    public function destroy($id)
    {
        $config = $this->lookupConfig();

        DB::table($config['table'])
            ->where($config['primary_key'], $id)
            ->delete();

        return $this->redirectWithMessage($config, 'destroy');
    }

    protected function lookupConfig(): array
    {
        $lookups = config('lookups');

        if (!isset($lookups[$this->lookupKey])) {
            throw new InvalidArgumentException("Unknown lookup table [{$this->lookupKey}].");
        }

        return $lookups[$this->lookupKey];
    }

    protected function payload(array $config, Request $request, bool $includeDefaults = false): array
    {
        $payload = [
            $config['display_field'] => $request->input($config['request_field']),
        ];

        if ($includeDefaults) {
            return array_merge($config['insert_defaults'] ?? [], $payload);
        }

        return $payload;
    }

    protected function findRecord(array $config, $id): object
    {
        $record = DB::table($config['table'])
            ->where($config['primary_key'], $id)
            ->first();

        if (!$record) {
            abort(404);
        }

        return $record;
    }

    protected function viewData(array $config, string $action, array $data = []): array
    {
        if (isset($config['titles'][$action])) {
            $data['title'] = $config['titles'][$action];
        }

        return $data;
    }

    protected function redirectWithMessage(array $config, string $action, ?Request $request = null)
    {
        $redirect = redirect()->route($config['route'] . '.index');
        $message = $config['messages'][$action] ?? null;

        if (!$message) {
            return $redirect;
        }

        if ($request && in_array($action, ['store', 'update'], true)) {
            $message = sprintf($message, $request->input($config['request_field']));
        }

        return $redirect->with('status', $message);
    }
}
