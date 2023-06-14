<?php
use Magento\Framework\App\Bootstrap;
require '../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create();

foreach ($productCollection as $product) {
    $productId = $product->getId();
    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
    $galleryEntries = $product->getMediaGalleryEntries();

    if (count($galleryEntries) > 1) {
        $secondEntry = $galleryEntries[1];
        $product->setData('hover_image', $secondEntry->getFile()); //  setting the second image from the product's media gallery as the hover_image
        $product->save();
        echo "Hover image set for product ID: " . $productId . ", ";
    }
}

echo "Completed updating hover images for all products.";