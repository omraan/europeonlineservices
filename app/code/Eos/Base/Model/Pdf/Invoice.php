<?php
namespace Eos\Base\Model\Pdf;

use Magento\Sales\Model\Order\Pdf\AbstractPdf;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Invoice extends AbstractPdf {

    /**
     * @var DateTime
     */

    protected $dateTime;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @param FileFactory $fileFactory
     * @param DateTime $dateTime
     */

    public function __construct(
        FileFactory $fileFactory,
        DateTime $dateTime
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
    }

    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('column1'), 'feed' => 35];
        $lines[0][] = ['text' => __('Column2'), 'feed' => 200, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

    }
    public function customPdf(){
        $this->_beforeGetPdf();
        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        $this->_afterGetPdf();
        return $pdf;
    }

    public function getPdf()
    {
        return $this->fileFactory->create(
            sprintf('invoice%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $this->customPdf(),
            DirectoryList::VAR_DIR,
            'application/pdf');

    }
}
