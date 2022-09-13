<?php

namespace Pentacom\Repgenerator\Domain\Gradient;

class GradientService
{
    /**
     * @param $pBegin
     * @param $pEnd
     * @param $pStep
     * @param $pMax
     * @return float|int
     */
    private function generateGradientsInterpolate($pBegin, $pEnd, $pStep, $pMax): float|int
    {
        if ($pBegin < $pEnd) {
            return (($pEnd - $pBegin) * ($pStep / $pMax)) + $pBegin;
        } else {
            return (($pBegin - $pEnd) * (1 - ($pStep / $pMax))) + $pEnd;
        }
    }

    public function generateGradients( $theColorBegin = 0x000000,  $theColorEnd = 0xffffff, int $theNumSteps = 10): array
    {
        //transform to hex, and get rid of # if exists
        $theColorBegin = hexdec(str_replace('#', '', $theColorBegin));
        $theColorEnd = hexdec(str_replace('#', '', $theColorEnd));

        //failsafe color codes
        $theColorBegin = (($theColorBegin >= 0x000000) && ($theColorBegin <= 0xffffff)) ? $theColorBegin : 0x000000;
        $theColorEnd = (($theColorEnd >= 0x000000) && ($theColorEnd <= 0xffffff)) ? $theColorEnd : 0xffffff;
        $theNumSteps = (($theNumSteps > 0) && ($theNumSteps < 256)) ? $theNumSteps : 16;

        $theR0 = ($theColorBegin & 0xff0000) >> 16;
        $theG0 = ($theColorBegin & 0x00ff00) >> 8;
        $theB0 = ($theColorBegin & 0x0000ff) >> 0;

        $theR1 = ($theColorEnd & 0xff0000) >> 16;
        $theG1 = ($theColorEnd & 0x00ff00) >> 8;
        $theB1 = ($theColorEnd & 0x0000ff) >> 0;

        $result = array();

        for ($i = 0; $i <= $theNumSteps; $i++) {
            $theR = $this->generateGradientsInterpolate($theR0, $theR1, $i, $theNumSteps);
            $theG = $this->generateGradientsInterpolate($theG0, $theG1, $i, $theNumSteps);
            $theB = $this->generateGradientsInterpolate($theB0, $theB1, $i, $theNumSteps);

            $theVal = ((($theR << 8) | $theG) << 8) | $theB;
            $result[] = sprintf('#%06X', $theVal);
        }

        return $result;
    }
}
