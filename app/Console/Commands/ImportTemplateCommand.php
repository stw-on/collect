<?php

namespace App\Console\Commands;

use App\Models\TransferTemplate;
use App\Models\TransferTemplateField;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportTemplateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'template:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a template from a YAML file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('file');

        if ($path === '-') {
            $data = yaml_parse(stream_get_contents(STDIN));
        } elseif (!file_exists($path)) {
            $this->error('File not found');
            return;
        } else {
            $data = yaml_parse_file($path);
        }

        DB::transaction(function () use ($data) {
            $template = new TransferTemplate();
            $template->fill($data);
            $template->save();

            foreach ($data['fields'] as $fieldData) {
                $field = new TransferTemplateField();
                $field->fill($fieldData);
                $field->template()->associate($template);
                $field->save();
            }

            $this->info('Template saved.');
            $this->info(url("/upload/" . $template->id));
        });
    }
}
