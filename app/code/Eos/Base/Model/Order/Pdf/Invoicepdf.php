<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Eos\Base\Model\Order\Pdf;

use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;

/**
 * Sales Order Invoice PDF model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Invoicepdf extends \Magento\Sales\Model\Order\Pdf\Invoice{


    public function getPdf($invoices = [])
    {
        //custom code of getpdf
        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $page = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
      //  $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA), 8);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        $width  = $page->getWidth(); //width for A4 page, 595
        $height = $page->getHeight(); //Height for A4 page,842
        $this->x = 30;
        $this->y = 800;
        $page->drawText('This is test example', $this->x, $this->y, 'UTF-8');


        //add new footer section this is our custom code function...
        $this->_drawFooter($page);

        $this->_afterGetPdf();
        return $pdf;
    }

    protected function _drawFooter(\Zend_Pdf_Page $page)
    {
        $this->y =50;
    //    $page->setFillColor(new \Zend_Pdf_Color_RGB(1, 1, 1));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(60, $this->y, 510, $this->y -30);

      //  $page->setFillColor(new \Zend_Pdf_Color_RGB(0.1, 0.1, 0.1));
        $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA), 7);
        $this->y -=10;
        $page->drawText("Company name", 70, $this->y, 'UTF-8');
        $page->drawText("Tel: +123 456 676", 230, $this->y, 'UTF-8');
        $page->drawText("Registered in Countryname", 430, $this->y, 'UTF-8');

    }

}
