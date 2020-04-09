<?php

function covid19ImpactEstimator($data)
{
  $impact = getStats($data);

  $severeImpact = getStats($data, 'severe');

  return ['data' => $data, 'impact' => $impact, 'severeImpact' => $severeImpact];
}

function getStats($data, $case = 'regular')
{
  $multiplier = $case === 'severe' ? 50 : 10;

  $currentlyInfected = $data['reportedCases'] * $multiplier;

  $durationInDays = getDurationInDays($data['timeToElapse'], $data['periodType']);

  $infectionsByRequestedTime = $currentlyInfected * (2 ** ($durationInDays % 2));

  $severeCasesByRequestedTime = 0.15 * $infectionsByRequestedTime;

  $hospitalBedsByRequestedTime = $severeCasesByRequestedTime - (0.35 * $data['totalHospitalBeds']);

  $casesForICUByRequestedTime = 0.05 * $infectionsByRequestedTime;

  $casesForVentilatorsByRequestedTime = 0.02 * $infectionsByRequestedTime;

  $dollarsInFlight = $infectionsByRequestedTime * $durationInDays * $data['avgDailyIncomeInUSD'] * $data['avgDailyIncomePopulation'];


  return [
    'currentlyInfected' => $currentlyInfected,
    'infectionsByRequestedTime' => $infectionsByRequestedTime,
    'severeCasesByRequestedTime' => $severeCasesByRequestedTime,
    'hospitalBedsByRequestedTime' => $hospitalBedsByRequestedTime,
    'casesForICUByRequestedTime' => $casesForICUByRequestedTime,
    'casesForVentilatorsByRequestedTime' => $casesForVentilatorsByRequestedTime,
    'dollarsInFlight' => number_format($dollarsInFlight, 2, '.', '')
  ];
}

function getDurationInDays($duration, $periodType)
{
  switch ($periodType) {
    case 'week':
      $computedDuration = $duration * 7;
      break;
    case 'month':
      $computedDuration = $duration * 30;
      break;
    default:
      $computedDuration = $duration;
  }
  return $computedDuration;
}
