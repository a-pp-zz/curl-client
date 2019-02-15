<?php
namespace AppZz\Http\CurlClient;
use AppZz\Helpers\Arr;

/**
 * Mimes dict
 */
class Mime {

    private $_mimes;

    function __construct ()
    {
        $this->_mimes['pdf']   = 'application/pdf';
        $this->_mimes['doc']   = 'application/msword';
        $this->_mimes['dot']   = 'application/msword';
        $this->_mimes['word']  = 'application/msword';
        $this->_mimes['w6w']   = 'application/msword';
        $this->_mimes['ai']    = 'application/postscript';
        $this->_mimes['eps']   = 'application/postscript';
        $this->_mimes['ps']    = 'application/postscript';
        $this->_mimes['rtf']   = 'application/rtf';
        $this->_mimes['docm']  = 'application/vnd.ms-word.document.macroEnabled.12';
        $this->_mimes['docx']  = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        $this->_mimes['dotm']  = 'application/vnd.ms-word.template.macroEnabled.12';
        $this->_mimes['dotx']  = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
        $this->_mimes['potm']  = 'application/vnd.ms-powerpoint.template.macroEnabled.12';
        $this->_mimes['potx']  = 'application/vnd.openxmlformats-officedocument.presentationml.template';
        $this->_mimes['ppam']  = 'application/vnd.ms-powerpoint.addin.macroEnabled.12';
        $this->_mimes['ppsm']  = 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12';
        $this->_mimes['ppsx']  = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
        $this->_mimes['pptm']  = 'application/vnd.ms-powerpoint.presentation.macroEnabled.12';
        $this->_mimes['pptx']  = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        $this->_mimes['xlam']  = 'application/vnd.ms-excel.addin.macroEnabled.12';
        $this->_mimes['xlsb']  = 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
        $this->_mimes['xlsm']  = 'application/vnd.ms-excel.sheet.macroEnabled.12';
        $this->_mimes['xlsx']  = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $this->_mimes['xltm']  = 'application/vnd.ms-excel.template.macroEnabled.12';
        $this->_mimes['xltx']  = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
        $this->_mimes['xla']   = 'application/vnd.ms-excel';
        $this->_mimes['xlc']   = 'application/vnd.ms-excel';
        $this->_mimes['xlm']   = 'application/vnd.ms-excel';
        $this->_mimes['xls']   = 'application/vnd.ms-excel';
        $this->_mimes['xlt']   = 'application/vnd.ms-excel';
        $this->_mimes['xlw']   = 'application/vnd.ms-excel';
        $this->_mimes['msg']   = 'application/vnd.ms-outlook';
        $this->_mimes['sst']   = 'application/vnd.ms-pkicertstore';
        $this->_mimes['cat']   = 'application/vnd.ms-pkiseccat';
        $this->_mimes['stl']   = 'application/vnd.ms-pkistl';
        $this->_mimes['pot']   = 'application/vnd.ms-powerpoint';
        $this->_mimes['pps']   = 'application/vnd.ms-powerpoint';
        $this->_mimes['ppt']   = 'application/vnd.ms-powerpoint';
        $this->_mimes['mpp']   = 'application/vnd.ms-project';
        $this->_mimes['gtar']  = 'application/x-gtar';
        $this->_mimes['gz']    = 'application/x-gzip';
        $this->_mimes['gzip']  = 'application/x-gzip';
        $this->_mimes['js']    = 'application/x-javascript';
        $this->_mimes['mdb']   = 'application/x-msaccess';
        $this->_mimes['crd']   = 'application/x-mscardfile';
        $this->_mimes['clp']   = 'application/x-msclip';
        $this->_mimes['dll']   = 'application/x-msdownload';
        $this->_mimes['m13']   = 'application/x-msmediaview';
        $this->_mimes['m14']   = 'application/x-msmediaview';
        $this->_mimes['mvb']   = 'application/x-msmediaview';
        $this->_mimes['wmf']   = 'application/x-msmetafile';
        $this->_mimes['mny']   = 'application/x-msmoney';
        $this->_mimes['pub']   = 'application/x-mspublisher';
        $this->_mimes['scd']   = 'application/x-msschedule';
        $this->_mimes['trm']   = 'application/x-msterminal';
        $this->_mimes['wri']   = 'application/x-mswrite';
        $this->_mimes['swf']   = 'application/x-shockwave-flash';
        $this->_mimes['ac3']   = 'audio/ac3';
        $this->_mimes['au']    = 'audio/basic';
        $this->_mimes['snd']   = 'audio/basic';
        $this->_mimes['mid']   = 'audio/midi';
        $this->_mimes['midi']  = 'audio/midi';
        $this->_mimes['mpa']   = 'audio/MPA';
        $this->_mimes['aif']   = 'audio/x-aiff';
        $this->_mimes['aifc']  = 'audio/x-aiff';
        $this->_mimes['aiff']  = 'audio/x-aiff';
        $this->_mimes['mp3']   = 'audio/x-mpeg';
        $this->_mimes['ra']    = 'audio/x-pn-realaudio';
        $this->_mimes['ram']   = 'audio/x-pn-realaudio';
        $this->_mimes['wav']   = 'audio/x-wav';
        $this->_mimes['bmp']   = 'image/bmp';
        $this->_mimes['gif']   = 'image/gif';
        $this->_mimes['ief']   = 'image/ief';
        $this->_mimes['jpe']   = 'image/jpeg';
        $this->_mimes['jpeg']  = 'image/jpeg';
        $this->_mimes['jpg']   = 'image/jpeg';
        $this->_mimes['pict']  = 'image/pict';
        $this->_mimes['png']   = 'image/png';
        $this->_mimes['tif']   = 'image/tiff';
        $this->_mimes['tiff']  = 'image/tiff';
        $this->_mimes['zip']   = 'multipart/x-zip';
        $this->_mimes['ics']   = 'text/calendar';
        $this->_mimes['ifb']   = 'text/calendar';
        $this->_mimes['css']   = 'text/css';
        $this->_mimes['csv']   = 'text/csv';
        $this->_mimes['txt']   = 'text/plain';
        $this->_mimes['rtx']   = 'text/richtext';
        $this->_mimes['tsv']   = 'text/tab-separated-values';
        $this->_mimes['mpe']   = 'video/mpeg';
        $this->_mimes['mpeg']  = 'video/mpeg';
        $this->_mimes['mpg']   = 'video/mpeg';
        $this->_mimes['avi']   = 'video/msvideo';
        $this->_mimes['mp4']   = 'video/mp4';
        $this->_mimes['mov']   = 'video/quicktime';
        $this->_mimes['qt']    = 'video/quicktime';
        $this->_mimes['flv']   = 'video/x-flv';
        $this->_mimes['movie'] = 'video/x-sgi-movie';
        $this->_mimes['cdr']   = 'application/coreldraw';
    }

    public function get ($ext = FALSE)
    {
        return $ext ? Arr::get ($this->_mimes, $ext) : $this->_mimes;
    }
}
