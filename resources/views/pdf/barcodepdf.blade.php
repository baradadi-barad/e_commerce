<?php
function generateUniqueNumber($length = 4)
{
    $uniqueId = uniqid();
    $uniqueNumber = rand(1000, 9999); //substr($uniqueId, -$length);

    return $uniqueNumber;
}
?>
<html>

<head>
    <title></title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<style>
    p.sku_info {
        margin-bottom: 0px !important;
        letter-spacing: 2px;
    }

    p.barcode_print {
        letter-spacing: 10px;
    }
</style>


<body>
    <div class="row col-md-12">
        <?php $count = 0;
        $i = 1; ?>
        @foreach ($stock as $barstr)
            <?php $i = $i + 1; ?>
            <div class="col-md-4 barcodeslist"
                style="width: 100%;height: auto;text-align: center !important;margin: 7px 0px 5px 0px !important; border-block-end: 1px dotted black; border-right: 1px dotted black; text-align: center; padding-bottom: 10px;">
                @php
                    $barcodestring = '';
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    
                    $barcodestring = $barstr;
                @endphp
                <p class="sku_info">SKU:{{ $productinfo['sku'] }}</p>
                {{-- {!! $generator->getBarcode($barcodestring, $generator::TYPE_CODE_128) !!} --}}
                <?php echo '<img  src="data:image/png;base64,' . base64_encode($generator->getBarcode($barcodestring, $generator::TYPE_CODE_128, 2, 50)) . '">';
                ?>
                <p class="barcode_print">{{ $barcodestring }}</p>
            </div>

            @if ($i % 39 == 0)
                <div style="page-break-before:always;"> </div>
            @endif
        @endforeach
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>
