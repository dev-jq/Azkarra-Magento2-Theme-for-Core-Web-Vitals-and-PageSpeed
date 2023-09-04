<?php
use Magento\Framework\App\Bootstrap;
require '../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

function importDemoData($objectManager, $model, $csvFile)
{
    $csv = $objectManager->create(\Magento\Framework\File\Csv::class);
    $rows = $csv->getData($csvFile);

    $columnMap = [
        'title' => 0,
        'identifier' => 1,
        'content' => 2,
        'is_active' => 3,
        'store_id' => 4
    ];

    if ($model === 'Magento\Cms\Model\Page') {
        $columnMap['page_layout'] = 5;
        $columnMap['url_key'] = 6;
    }

    $dataModel = $objectManager->get($model);

    $existingElement = null;

    $importedCount = 0;

    foreach ($rows as $index => $row) {

        if ($index === 0) {
            continue;
        }

        $data = [];
        foreach ($columnMap as $field => $columnIndex) {

            if (isset($row[$columnIndex])) {
                $data[$field] = $row[$columnIndex];
            } else {
                $data[$field] = '';
            }
        }

        $identifier = $data['identifier'];

        if ($model === 'Magento\Cms\Model\Page') {
            $blockRepository = $objectManager->get(\Magento\Cms\Model\PageRepository::class);
            try {
                $existingElement = $blockRepository->getById($identifier);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $existingElement = null;
            }
        } else if ($model === 'Magento\Cms\Model\Block') {
            $blockRepository = $objectManager->get(\Magento\Cms\Model\BlockRepository::class);
            try {
                $existingElement = $blockRepository->getById($identifier);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $existingElement = null;
            }
        }

        if ($existingElement !== null) {
            echo "The item with identifier '$identifier' already exists. I skip adding.\n";
            continue;
        }

        if ($model === 'Magento\Cms\Model\Page' && isset($data['page_layout'])) {
            $data['page_layout'] = $data['page_layout'];
        }

        try {
            $dataModel->setData($data)->save();
            $importedCount++;
        } catch (Exception $e) {
            echo 'Error: ' . $e;
        }
    }

    echo "Data import for $model from file $csvFile completed.\n";
    echo "Imported $importedCount items.\n";
}

importDemoData($objectManager, 'Magento\Cms\Model\Block', 'demo_data/blocks.csv');
importDemoData($objectManager, 'Magento\Cms\Model\Page', 'demo_data/pages.csv');