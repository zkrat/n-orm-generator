<?php
namespace NOrmGenerator\TracyAddon;

use Latte\Engine;
use Tracy\IBarPanel;

class BarReport implements IBarPanel{

	private static $class;

	protected $tempDir;

	protected static $barReport=null;

	protected $generatedFiles=[];

	protected function __construct($tempDir) {
		$this->tempDir=$tempDir;
		self::$barReport=$this;
	}

	public function addFile($fileName){
		if(!isset($this->generatedFiles[$fileName]))
			$this->generatedFiles[$fileName]=1;
		else
			$this->generatedFiles[$fileName]++;
	}

	/**
	 * @return BarReport
	 */
	public static function getBarReport(): BarReport {
		return self::$barReport;
	}

	/**
	 * @return bool
	 */
	public static function hasBarReport(): bool {
		return self::$barReport instanceof BarReport;
	}

	public function getTab()
	{

		$img ='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA4xpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1Nzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpjOTM1N2E0MS0zNWEzLTRmYTMtODU1OS0zZDExMjA4ZWIzMGQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6QjExRTdDN0EwN0JEMTFFQUJDMDZDNzRDMDk2RkRCMzciIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6QjExRTdDNzkwN0JEMTFFQUJDMDZDNzRDMDk2RkRCMzciIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIEVsZW1lbnRzIDE0LjAgKE1hY2ludG9zaCkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo5ZWQzN2Y1OC03MjU2LTQ4MjgtYWM0Ny1lMTQ5OTllYTgxZmQiIHN0UmVmOmRvY3VtZW50SUQ9ImFkb2JlOmRvY2lkOnBob3Rvc2hvcDo3MzI0NDBkNy01MDI1LTExN2QtODZhMi1lNGNlNTAwMDAwMDAiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz4BNzDBAAAC1klEQVR42pRTS08TURT+7sxty7TQQq0IFeRRSQkPJSZIMERJIMGwUBOJutIVLtwgrlwY4wYXxrjwB7gw0YWycQMLjaIJDwUTTGO0i5oSoLxKoUgfQ2fmemZokISF8UzOzb333PPd831nLhNCILQswABsZNCYzrG23yoaGcNJ7DOKzxXaMeu0i6kiu5iWJYamMgZuBlUNgegGe7qpolsXsJtgAgdtLQNIYLrbwd7VesVd2pq1ANbT7PpqGr12GZAZ/mVyIouegiT7jgrMSvlNO/7D8ndIe0ORQ7woVTBFtGCI3fJJGmturcXftZlcUoDpQy4xbOZaFN7G0KLlcs9PlLAnCuf1mRwCJGIzOTMTKNH85hUbfuaEHgpvCWVmXQ92VDvGLYDINjs78sPWf8SDUG0xJiRNDTf45BlfId82ARJpzRmOa94cLyiPJuU7i5s41VKG1xR6ZgGQcDtmbaspNK9uoxnMgYmV/azpmOAWDSbtUuQS1D0Kbo5lkecnyQdFs1HSxQhQPQZslQOvuoFjCk9+vqCXWQCtPu1lsg6lX1d5TzyD4wbhGDQIc0KolxeAa4+AjJPa9QVoUxHzeFGlzci3LIDJNX5zKWWUd/kzjwtscmompvqE7HA2lNm97VG0Vw2hI3JfH51r1t5UxliwfsA+qAF+nfAtgLQO1/Si1DcbV/rcDsQkg89XeKTlpRSyrlEENh4g8bCSebRtx70aGRVXuoBCA7GAjA8WALWJS/lfKpmFn4D9iZXd3svnqewioIlJZybjwDfKiPYBQ43QpSJLUyDoNj5Sjw1D301CXkzTB+lJDVQDncWATppwit8IkKAuVNL5c8x8jSO/gHBC7/w0L67OpXjbZhY16RxcBGAbbqEK6JpIxsrPHHUATgmFdMcWeb9FQaOI34Wx1sPqeIsvVzuxoAWXVaWkt46XUtU90SQWT7txiTModHyHPER+m/z9HwEGAFCjEmtz8iTZAAAAAElFTkSuQmCC';
		$imgHtml='<img src="'.$img.'">';
		return $imgHtml.' nORM';
    }

	public function getPanel()
	{

		$latte = new Engine();
		$latte->setTempDirectory($this->tempDir.'/classModelGeneratorBarReport');
		$parameters = [
			'generatedFiles' => $this->generatedFiles,
		];

		return $latte->renderToString(__DIR__.'/template/barReport.latte',$parameters);
    }


    public static function register($tempDir){
	    self::$class =new static($tempDir);
	    return self::$class;
	}
}